<?php

namespace Faq\Services;

use Faq\Faq;
use Faq\FaqUtils;
use Faq\Repositories\RepositoryInterface;

class FaqList
{
    protected RepositoryInterface $repository;
    protected int $start;
    protected string $type;
    protected FaqUtils $utils;

    public const ID = 'faq_list';

    public function __construct(RepositoryInterface $repository, int $start = 0)
    {
        $this->repository = $repository;
        $this->start = $start;
        $this->utils = new FaqUtils();
    }

    public function build(string $message = ''): void
    {
        global $scripturl;

        $maxIndex = $this->repository->count();
        $start = $this->start;
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

            'title' => $this->utils->text('$add'),
            'base_href' => $scripturl . '?action=' . $action,
            'items_per_page' => 10,
            'get_count' => [
                'function' => fn() => $maxIndex,
            ],
            'get_items' => [
                'function' => fn($start, $maxIndex) => $this->repository->getAll($start, $maxIndex),
                'params' => [
                    $start,
                    $maxIndex,
                ],
            ],
            'no_items_label' => $this->utils->text('no_search_results'),
            'columns' => [
                'id' => [
                    'header' => [
                        'value' => $this->utils->text('edit_id'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => $rowData->getId(),
                    ],
                ],
                'name' => [
                    'header' => [
                        'value' => $this->utils->text('edit_name'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => $rowData->getName(),
                    ],
                ],
                'edit' => [
                    'header' => [
                        'value' => $this->utils->text('edit'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => strtr($anchor, [
                            '{href}' => $scripturl . '?action=' . $action .';sa=add;id=' . $rowData->getId(),
                            '{title}' => $this->utils->text('edit'),
                        ]),
                    ],
                ],
                'delete' => [
                    'header' => [
                        'value' => $this->utils->text('delete'),
                    ],
                    'data' => [
                        'function' => fn($rowData) => strtr($anchor, [
                            '{href}' => $scripturl . '?action=' . $action .';sa=delete;id=' . $rowData->getId(),
                            '{title}' => $this->utils->text('delete'),
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
                        ' . $this->utils->text($add) . '</a>',
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

    protected function getStringByType(string $textKey): string
    {

        return $this->utils->text($textKey);
    }
}