<?php

namespace Faq\Controllers;

use Faq\Entities\FaqEntity;
use Faq\Faq as Faq;
use Faq\FaqRequest;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\FaqRepository;

class FaqController extends BaseController
{
    public const ACTION = 'faq';
    public const SUB_ACTIONS = [
        'index',
        'add',
        'delete',
        'search',
        'single',
    ];

    protected ?FaqRepository $repository;
    protected CategoryRepository $categoryRepository;

    public function __construct(
        ?FaqRequest $request = null,
        ?FaqRepository $repository = null,
        CategoryRepository $categoryRepository = null)
    {
        $this->repository = $repository ?? new FaqRepository();
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();

        parent::__construct($request);
    }

    public function add(): void
    {
        global $sourcedir;

        // Needed for the WYSIWYG editor.
        require_once($sourcedir . '/Subs-Editor.php');

        $id = $this->request->get('id');
        $entity = $id ? $this->repository->getById($id) : $this->repository->getEntity();

        create_control_richedit([
            'id' => FaqEntity::BODY,
            'value' => $entity->getBody(),
            'height' => '175px',
            'width' => '100%',
            'required' => true,
        ]);

        $this->setTemplateVars([
            'entity' => $entity,
            'categories' => $this->categoryRepository->getAll(),
        ]);

        if ($this->request->isPost()) {
            $data = array_intersect_key($this->request->all(), FaqEntity::COLUMNS);

            if ($this->request->isSet('preview')) {
                $this->redirect($this->buildPreview($entity, $data));
            }

            if (!$this->validation->isValid($this->repository->getEntity(), $data)) {

            }

            $this->save($data, $id);
        }
    }

    protected function buildPreview(FaqEntity $entity, array $data): string
    {
        $this->setTemplateVars([
            'entity' => $entity->setEntity($data),
        ]);
    }

    public function index(): void
    {
        $this->setTemplateVars([
            'entities' => $this->repository->getAll(),
        ]);
    }

    public function delete(): void
    {
        $result = 'error';
        $id = $this->request->get('id');

        if ($id) {
            $this->repository->delete([$id]);
            $result = 'success';
        }

        $this->redirect(sprintf(Faq::NAME . '_%s_' . 'delete', $result));
    }

    protected function save(array $data, int $id): void
    {
        $call = $id ? 'update' : 'insert';

        $this->repository->{$call}($data, $id);

        $this->redirect(Faq::NAME . '_success_'. $call);
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