<?php

namespace Faq\Controllers;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\FaqAdmin;
use Faq\FaqRequest;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\RepositoryInterface;
use Faq\Services\FaqList;

class CategoryController extends BaseController
{
    public const ACTION = 'faqCategory';
    public const SUB_ACTION_MANAGE = 'manage';
    public const SUB_ACTION_ADD = 'add';
    public const SUB_ACTION_DELETE = 'delete';
    public const SUB_ACTIONS = [
        self::SUB_ACTION_MANAGE,
        self::SUB_ACTION_ADD,
        self::SUB_ACTION_DELETE,
    ];

    public function __construct(
        ?FaqRequest $request = null,
        ?RepositoryInterface $categoryRepository = null)
    {
        $this->repository = $categoryRepository ?? new CategoryRepository();

        parent::__construct($request);
    }

    public function manage(): void
    {
        global $context;

        isAllowedTo([Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD, Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE]);

        $context['sub_template'] = 'show_list';
        $context['default_list'] = 'faq_list';

        $categoryList = new FaqList($this->repository, $this->request->get('start', 0));
        $categoryList->build($context[Faq::NAME]['message']);
    }

    public function add(): void
    {
        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_ADD);

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

    public function delete(): void
    {
        $id = $this->request->get('id');

        if (!$id) {
            $this->redirect();
        }

        isAllowedTo(Faq::NAME . '_' . FaqAdmin::PERMISSION_DELETE);

        // Can't delete the default one
        if ($id === CategoryEntity::DEFAULT_CATEGORY_ID) {
            $this->redirect(self::ERROR, 'delete_default');
        }

        $this->repository->delete([$id]);

        // Update all FAQs with this category to a default one
        $this->repository->setDefaultCategoryId($id);

        $this->redirect(self::SUCCESS, 'delete');
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