<?php

namespace Faq\Controllers;

use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\FaqAdmin;
use Faq\FaqConfig;
use Faq\FaqParser;
use Faq\FaqRequest;
use Faq\FaqTranslator;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\CategoryRepositoryInterface;
use Faq\Repositories\FaqRepository;
use Faq\Repositories\FaqRepositoryInterface;
use Faq\Services\FaqList;
use Faq\Services\FaqValidation;

class FaqController extends BaseController
{
    public const ACTION = 'faq';

    public const SUB_ACTION_INDEX = 'index';
    public const SUB_ACTION_ADD = 'add';
    public const SUB_ACTION_DELETE = 'delete';
    public const SUB_ACTION_SEARCH = 'search';
    public const SUB_ACTION_SINGLE = 'single';
    public const SUB_ACTION_MANAGE = 'manage';
    public const SUB_ACTION_CATEGORY = 'category';
    public const SUB_ACTIONS = [
        self::SUB_ACTION_INDEX,
        self::SUB_ACTION_ADD,
        self::SUB_ACTION_DELETE,
        self::SUB_ACTION_SEARCH,
        self::SUB_ACTION_SINGLE,
        self::SUB_ACTION_MANAGE,
        self::SUB_ACTION_CATEGORY,
    ];

    /** SMF token name for the add/edit form, single use. */
    public const TOKEN_ACTION = Faq::NAME . '-' . self::SUB_ACTION_ADD;

    protected FaqRepositoryInterface $faqRepository;
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        ?FaqRequest $request = null,
        ?FaqRepositoryInterface $repository = null,
        ?CategoryRepositoryInterface $categoryRepository = null)
    {
        // Built first: setTemplateVars() below already needs the translator and the parser.
        parent::__construct($request);

        $this->faqRepository = $repository ?? new FaqRepository();
        $this->categoryRepository  = $categoryRepository ?? new CategoryRepository();

        // Inherited members (save()) work against the generic contract.
        $this->repository = $this->faqRepository;

        $this->setTemplateVars([
            'categories' => $this->categoryRepository->getAll(false)['entities'],
        ]);
    }

    public function index(): void
    {
        global $context;

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW);

        $start = $this->request->get('start', 0);
        $data = $this->faqRepository->getAll(true, $start);

        $this->setTemplateVars([
            'entities' => $data['entities'],
            'pagination' => $this->faqRepository->buildPagination($start, $context['current_url'], $data['total']),
        ]);
    }

    public function search(): void
    {
        global $context;

        if (!$this->request->isPost()) {
            $this->redirect('', 'index');
        }

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW);

        $searchValue = $this->request->get('search_value');

        if (empty($searchValue)) {
            $this->redirect(self::ERROR, 'index');
        }

        $title = strtr($this->translator->text('search_title'),
            ['{searchValue}' => $searchValue]);
        $start = $this->request->get('start', 0);
        $data = $this->faqRepository->searchBy($searchValue, $start);

        $this->setTemplateVars([
            'entities' => $data['entities'],
            'pagination' => $this->faqRepository->buildPagination($start, $context['current_url'], $data['total']),
        ], [
            'sub_template' => Faq::NAME . '_index',
            'page_title' => $title,
        ]);
        $this->overrideLinkTree($title);
    }

    public function add(): void
    {
        global $sourcedir, $txt;

        // Needed for the WYSIWYG editor.
        require_once($sourcedir . '/Subs-Editor.php');

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD);

        $id = $this->request->get('id');
        $entity = $id ? $this->faqRepository->getById($id) : $this->faqRepository->getEntity();
        $templateVars = [
            'entity' => $entity,
            'categories' => $this->categoryRepository->getAll()['entities'],
            'errors' => '',
            'preview' => [],
        ];

        if ($this->request->isPost()) {
            $this->verifyToken(self::TOKEN_ACTION);

            $data = array_intersect_key($this->request->all(), FaqEntity::COLUMNS);
            
            if ($this->request->isSet('preview')) {
                $templateVars['preview'] = $this->parser->parse($data);
            }

            $entity->setEntity($data);
            $errorMessage = $this->validation->isValid($entity, $data);

            if ($errorMessage) {
                $templateVars['errors'] = $errorMessage;
            }

            if ($this->request->isSet('save') && empty($errorMessage)) {
                $data[FaqEntity::LOG] = $this->upsertLog($entity->getLog());
                $this->save($data, $id);
            }
        }

        create_control_richedit([
            'id' => FaqEntity::BODY,
            'value' => $entity->getBody(),
            'height' => '185px',
            'width' => '100%',
            'required' => true,
        ]);

        $this->setTemplateVars($templateVars);

        // Tokens are single use, a fresh one is needed for every render.
        $this->createSessionToken(self::TOKEN_ACTION);
    }

    public function single(): void
    {
        $id = $this->request->get('id');

        if (!$id) {
            $this->redirect('', 'index');
        }

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW);

        /** @var FaqEntity $entity */
        $entity = $this->faqRepository->getById($id);

        $this->setTemplateVars([
            'entities' => [$entity],
        ], [
            'sub_template' => Faq::NAME . '_index',
            'page_title' => $entity->getName(),
        ]);
        $this->overrideLinkTree($entity->getName());
    }

    public function delete(): void
    {
        $result = self::ERROR;
        $id = $this->request->get('id');

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE);

        // Deletes are plain GET links, the session check is what stops CSRF.
        $this->verifySession('get');

        if ($id) {
            $this->faqRepository->delete([$id]);
            $result = self::SUCCESS;
        }

        $this->redirect($result, 'delete');
    }

    public function manage(): void
    {
        global $context;

        isAllowedTo([Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD, Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]);

        $context['sub_template'] = 'show_list';
        $context['default_list'] = 'faq_list';

        $faqList = new FaqList($this->repository, $this->request->get('start', 0));
        $faqList->build($context[Faq::NAME]['message']);
    }

    public function category(): void
    {
        global $context;

        $id = $this->request->get('id');

        if (!$id) {
            $this->redirect('', 'index');
        }

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW);

        $category = $this->categoryRepository->getById($id);
        $start = $this->request->get('start', 0);
        $data = $this->faqRepository->getByCatId($id, $start);

        $this->setTemplateVars([
            'entities' => $data['entities'],
            'pagination' => $this->faqRepository->buildPagination($start, $context['current_url'], $data['total']),
            'category' => $category,
        ], [
            'sub_template' => Faq::NAME . '_index',
            'page_title' => strtr($this->translator->text('by_category'), [
                '{categoryName}' => $category->getName(),
            ]),
        ]);
        $this->overrideLinkTree(strtr($this->translator->text('by_category'), [
            '{categoryName}' => $category->getName(),
        ]));
    }

    protected function upsertLog($logs = []) :string
    {
        global $user_info;

        $logs[(int) $user_info['id']] = time();

        return json_encode($logs);
    }

    protected function getSubActions(): array
    {
        return self::SUB_ACTIONS;
    }

    protected function getAction(): string
    {
        return self::ACTION;
    }

    public function getDefaultSubAction():string
    {
        return self::SUB_ACTIONS[0];
    }
}