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
use Faq\Repositories\FaqRepository;
use Faq\Repositories\RepositoryInterface;
use Faq\Services\FaqList;
use Faq\Services\FaqService;
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

    protected ?FaqService $service;
    protected ?FaqRequest $request;
    protected ?FaqParser $parser;

    public function __construct(
        ?FaqRequest $request = null,
        ?FaqService $service = null,
        ?FaqValidation $validation = null,
        ?FaqConfig $config = null,
        ?FaqTranslator $translator = null,
        ?FaqParser $parser = null
    ) {
        $this->service = $service ?? new FaqService();
        $this->request = $request ?? new FaqRequest();
        $this->parser = $parser ?? new FaqParser();

        $this->setTemplateVars([
            'categories' => $this->service->getAllCategories(),
            'latest' => $this->service->getLatest()
        ]);

        parent::__construct($request, $validation, $config, $translator);
    }

    public function index(): void
    {
        $this->checkAllowedTo(self::SUB_ACTION_INDEX);

        $start = $this->request->get('start', 0);
        $data = $this->service->getAll($start);

        $this->setTemplateVars($data);
    }

    public function search(): void
    {
        if (!$this->request->isPost()) {
            $this->redirect('', self::SUB_ACTION_INDEX);
        }

        $this->checkAllowedTo(self::SUB_ACTION_SEARCH);

        $searchValue = $this->request->get('search_value');

        if (empty($searchValue)) {
            $this->redirect(self::ERROR, self::SUB_ACTION_INDEX);
        }

        $title = strtr($this->translator->text('search_title'),
            ['{searchValue}' => $searchValue]);
        $start = $this->request->get('start', 0);
        $data = $this->service->searchBy($searchValue, $start);
        $data['pagination'] = $this->service->buildPagination($start, $data['total']);

        $this->setTemplateVars($data, [
            'sub_template' => Faq::NAME . '_index',
            'page_title' => $title,
        ]);
        $this->overrideLinkTree($title);
    }

    public function add(): void
    {
        global $sourcedir;

        // Needed for the WYSIWYG editor.
        require_once($sourcedir . '/Subs-Editor.php');

        $this->checkAllowedTo(self::SUB_ACTION_ADD);

        $id = $this->request->get(FaqEntity::ID, 0);
        $entity = $this->service->getById($id);
        $templateVars = [
            'entity' => $entity,
            'categories' => $this->categoryRepository->getAll()['entities'],
            'errors' => '',
            'preview' => [],
        ];

        if ($this->request->isPost()) {
            validateToken(Faq::NAME . '-' . self::SUB_ACTION_ADD);

            $data = array_intersect_key($this->request->all(), FaqEntity::COLUMNS);
            
            if ($this->request->isSet('preview')) {
                $templateVars['preview'] = $this->parser->parse($data);
            }

            $entity->setEntity($data);
            $errorMessage = $this->validation->isValid($this->repository->getEntity(), $data);

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

        createToken(Faq::NAME . '-' . self::SUB_ACTION_ADD);
    }

    public function single(): void
    {
        $id = $this->request->get(FaqEntity::ID);

        if (!$id) {
            $this->redirect('', self::SUB_ACTION_INDEX);
        }

        $this->checkAllowedTo(self::SUB_ACTION_SINGLE);

        /** @var FaqEntity $entity */
        $entity = $this->service->getById($id);

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

        $this->checkAllowedTo(self::SUB_ACTION_DELETE);

        if ($id) {
            $this->repository->delete([$id]);
            $result = self::SUCCESS;
        }

        $this->redirect($result, 'delete');
    }

    public function manage(): void
    {
        global $context;

        $this->checkAllowedTo(self::SUB_ACTION_MANAGE);

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
        $data = $this->repository->getByCatId($id, $start);

        $this->setTemplateVars([
            'entities' => $data['entities'],
            'pagination' => $this->repository->buildPagination($start, $context['current_url'], $data['total']),
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

    protected function checkAllowedTo(string $action)
    {
        $toCheck = match ($action) {
            self::SUB_ACTION_MANAGE => [
                Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
                Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE
            ],
            self::SUB_ACTION_INDEX,
            self::SUB_ACTION_CATEGORY,
            self::SUB_ACTION_SINGLE,
            self::SUB_ACTION_SEARCH => Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW,
            self::SUB_ACTION_ADD    => Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD,
            default                 => Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE,
        };

        isAllowedTo($toCheck);
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
