<?php

namespace Faq\Controllers;

use Faq\Faq as Faq;
use Faq\Repositories\FaqRepository;
use Faq\Request;

class FaqController extends BaseController
{
    public const ACTION = __CLASS__;
    public const SUB_ACTIONS = [
        'add',
        'delete',
        'manage',
        'search',
        'single',
    ];

    protected ?FaqRepository $repository;

    public function __construct(?Request $request = null, ?FaqRepository $repository = null)
    {
        $this->repository = $repository ?? new FaqRepository();
        parent::__construct($request);
    }

    public function add(): void
    {
        $id = $this->request->get('id');

        $this->setTemplateVars([
            'entity' => $id ? $this->repository->getById($id) : [],
        ]);

        if ($this->request->isPost()) {
            // validate
            $data = $this->request->get(Faq::NAME);

            $this->save($data, $id);
        }
    }

    public function manage(): void
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
}