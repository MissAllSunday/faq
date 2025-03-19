<?php

namespace Faq\Controllers;

use Faq\Entities\CategoryEntity;
use Faq\FaqRequest;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\RepositoryInterface;
use Faq\Services\FaqList;

class CategoryController extends BaseController
{
    public const ACTION = 'faqCategory';
    public const SUB_ACTIONS = [
        'index',
        'add',
        'delete',
        'categories',
    ];
    protected RepositoryInterface $repository;

    public function __construct(
        ?FaqRequest $request = null,
        ?RepositoryInterface $categoryRepository = null)
    {
        $this->repository = $categoryRepository ?? new CategoryRepository();

        parent::__construct($request);
    }

    public function index(): void
    {
        global $context;

        $context['sub_template'] = 'show_list';
        $context['default_list'] = 'faq_list';
        $start = $this->request->get('start', 0);

        $categoryList = new FaqList($this->repository, $start);
        $categoryList->build($this->showMessage());
    }

    public function add(): void
    {

        $id = $this->request->get('id');

        $entity = $id ? $this->repository->getById($id) : $this->repository->getEntity();
        $templateVars = [
            'entity' => $entity,
            'errors' => '',
            'preview' => [],
        ];

        if ($this->request->isPost()) {
            $data = array_intersect_key($this->request->all(), CategoryEntity::COLUMNS);

            $entity->setEntity($data);
            $errorMessage = $this->validation->isValid($this->repository->getEntity(), $data);

            if ($errorMessage) {
                $templateVars['errors'] = $errorMessage;
            }

            if ($this->request->isSet('save') && empty($errorMessage)) {
                $this->save($data, $id);
            }
        }

        $this->setTemplateVars($templateVars);
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