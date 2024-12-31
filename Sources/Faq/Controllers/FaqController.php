<?php

namespace Faq\Controllers;

use Faq\Faq as Faq;
use Faq\Repositories\FaqRepository;
use Faq\Request;

class FaqController extends Base
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
        global $context;

        $id = $this->request->get('id');

        $this->setTemplateVars([
            'entry' => $id ? $this->repository->getById($id) : [],
        ]);

        if ($this->request->isPost()) {
            $data = $this->request->get(Faq::NAME);
            $this->save($data, $id);
        }
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