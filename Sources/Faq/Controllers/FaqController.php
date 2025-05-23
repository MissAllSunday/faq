<?php

namespace Faq\Controllers;

use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\FaqAdmin;
use Faq\FaqRequest;
use Faq\FaqUtils;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\FaqRepository;
use Faq\Repositories\RepositoryInterface;
use Faq\Services\FaqList;

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

    protected RepositoryInterface $repository;
    protected CategoryRepository $categoryRepository;
    protected FaqUtils $utils;

    public function __construct(
        ?FaqRequest $request = null,
        ?FaqRepository $repository = null,
        CategoryRepository $categoryRepository = null)
    {
        $this->repository = $repository ?? new FaqRepository();
        $this->categoryRepository  = $categoryRepository ?? new CategoryRepository();
        $this->utils = new FaqUtils();

        $this->setTemplateVars([
            'categories' => $this->categoryRepository->getAll(false)['entities'],
        ]);

        parent::__construct($request);
    }

    public function index(): void
    {
        global $context;

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW);

        $start = $this->request->get('start', 0);
        $data = $this->repository->getAll(true, $start);

        $this->setTemplateVars([
            'entities' => $data['entities'],
            'pagination' => $this->repository->buildPagination($start, $context['current_url'], $data['total']),
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

        $title = strtr($this->utils->text('search_title'),
            ['{searchValue}' => $searchValue]);
        $start = $this->request->get('start', 0);
        $data = $this->repository->searchBy($searchValue, $start);

        $this->setTemplateVars([
            'entities' => $data['entities'],
            'pagination' => $this->repository->buildPagination($start, $context['current_url'], $data['total']),
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
        $entity = $id ? $this->repository->getById($id) : $this->repository->getEntity();
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
                $templateVars['preview'] = $this->utils->parse($data);
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
        $id = $this->request->get('id');

        if (!$id) {
            $this->redirect('', 'index');
        }

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_VIEW);

        /** @var FaqEntity $entity */
        $entity = $this->repository->getById($id);

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

        if ($id) {
            $this->repository->delete([$id]);
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
        $data = $this->repository->getByCatId($id, $start);

        $this->setTemplateVars([
            'entities' => $data['entities'],
            'pagination' => $this->repository->buildPagination($start, $context['current_url'], $data['total']),
            'category' => $category,
        ], [
            'sub_template' => Faq::NAME . '_index',
            'page_title' => strtr($this->utils->text('by_category'), [
                '{categoryName}' => $category->getName(),
            ]),
        ]);
        $this->overrideLinkTree(strtr($this->utils->text('by_category'), [
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