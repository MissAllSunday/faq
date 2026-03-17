<?php

namespace Faq\Services;

use Faq\Controllers\CategoryController;
use Faq\Controllers\FaqController;
use Faq\Entities\CategoryEntity;
use Faq\Faq;
use Faq\FaqAdmin;
use Faq\FaqConfig;
use Faq\FaqTranslator;
use Faq\FaqUtils;
use Faq\Repositories\RepositoryInterface;

class FaqList
{
    protected RepositoryInterface $repository;
    protected int $start;
    protected string $type;
    protected FaqTranslator $translator;
    protected FaqConfig $config;

    public const ID = 'faq_list';

    public function __construct(
        RepositoryInterface $repository, 
        int $start = 0,
        ?FaqTranslator $translator = null,
        ?FaqConfig $config = null
    ) {
        $this->repository = $repository;
        $this->start = $start;
        $this->translator = $translator ?? new FaqTranslator();
        $this->config = $config ?? new FaqConfig();
    }

    public function build(string $message = ''): void
    {
        global $scripturl;

        $start = $this->start;
        $data = $this->repository->getAll(true, $start);
        $anchor = '<a href="{href}" class="you_sure">{title}</a>';

        switch ($this->repository->getTable()) {
            case 'faq_categories':
                $action = 'faqCategory';
                $add = 'category_add_title';
                break;
            default:
                $action = 'faq';
                $add = 'add_title';
        }

        $listOptions = [
            'id' => self::ID,
            'title' => $this->translator->text('$add'),
            'base_href' => $scripturl . '?action=' . $action . ';sa=manage',
            'items_per_page' => $this->config->setting(FaqAdmin::SETTINGS_PAGINATION, 0),
            'get_count' => [
                'value' => $data['total'],
            ],
            'get_items' => [
                'value' => $data['entities'],
            ],
            'no_items_label' => $this->translator->text('no_search_results'),
            'columns' => [
                'id' => [
                    'header' => [
                        'value' => $this->translator->text('edit_id'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => $rowData->getId(),
                    ],
                ],
                'name' => [
                    'header' => [
                        'value' => $this->translator->text('edit_name'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => $rowData->getName(),
                    ],
                ],
                'edit' => [
                    'header' => [
                        'value' => $this->translator->text('edit'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => strtr($anchor, [
                            '{href}' => $scripturl . '?action=' . $action .';sa=add;id=' . $rowData->getId(),
                            '{title}' => $this->translator->text('edit'),
                        ]),
                    ],
                ],
                'delete' => [
                    'header' => [
                        'value' => $this->translator->text('delete'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => ($action === CategoryController::ACTION &&
                            $rowData->getId() === CategoryEntity::DEFAULT_CATEGORY_ID) ?
                            $this->translator->text('delete') :
                            strtr($anchor, [
                            '{href}' => $scripturl . '?action=' . $action .';sa=delete;id=' . $rowData->getId(),
                            '{title}' => $this->translator->text('delete'),
                        ]),
                    ],
                ],
            ],
            'additional_rows' => [
                [
                    'position' => 'top_of_list',
                    'value' => $message,
                ],
                [
                    'position' => 'bottom_of_list',
                    'value' => '<a class="button" type="submit" href="'. $scripturl . '?action=' . $action .';sa=add">
                        ' . $this->translator->text($add) . '</a>',
                ],
            ],
        ];

        $this->createList($listOptions);
    }

    protected function createList(array $listOptions): void
    {
        global $sourcedir;

        require_once($sourcedir . '/Subs-List.php');

		createList($listOptions);
    }
}
