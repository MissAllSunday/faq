<?php

namespace Faq\Controllers;

use Faq\Entities\FaqEntity;
use Faq\FaqRequest;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\FaqRepository;
use Faq\Repositories\RepositoryInterface;

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

    protected RepositoryInterface $repository;
    protected RepositoryInterface $categoryRepository;

    public function __construct(
        ?FaqRequest $request = null,
        ?RepositoryInterface $repository = null,
        RepositoryInterface $categoryRepository = null)
    {
        $this->repository = $repository ?? new FaqRepository();
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();

        parent::__construct($request);
    }

    public function index(): void
    {
        $this->setTemplateVars([
            'entities' => $this->repository->getAll(),
            'message' => $this->showMessage()
        ]);
    }

    public function add(): void
    {
        global $sourcedir, $txt;

        // Needed for the WYSIWYG editor.
        require_once($sourcedir . '/Subs-Editor.php');

        $id = $this->request->get('id');
        $entity = $id ? $this->repository->getById($id) : $this->repository->getEntity();
        $templateVars = [
            'entity' => $entity,
            'categories' => $this->categoryRepository->getAll(),
            'errors' => '',
            'preview' => [],
        ];

        if ($this->request->isPost()) {
            $data = array_intersect_key($this->request->all(), FaqEntity::COLUMNS);
            
            if ($this->request->isSet('preview')) {
                $templateVars['preview'] = $this->buildPreview($data);
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
    }

    protected function buildPreview(array $data): array
    {
        global $sourcedir;

        require_once($sourcedir.'/Subs-Post.php');

        $data['title'] = $data['title'] ?? '';
        $data['body'] = $data['body'] ?? '';

        censorText($data['title']);
        preparsecode($data['body'], true);
        $data['body'] = parse_bbc($data['body']);

        return $data;
    }

    public function delete(): void
    {
        $result = self::ERROR;
        $id = $this->request->get('id');

        if ($id) {
            $this->repository->delete([$id]);
            $result = self::SUCCESS;
        }

        $this->redirect($result, 'delete');
    }

    protected function upsertLog($logs = []) :string
    {
        global $user_info;

        $logs[(int) $user_info] = time();

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