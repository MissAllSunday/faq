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

    public function build(): void
    {
        global $scripturl;

        $maxIndex = $this->repository->count();
        $start = $this->start;
        $anchor = '<a href="{href}">{title}</a>';

        switch ($this->repository->getTable()) {
            case 'faq_categories':
                $sendText = $this->utils->text('addcat_send');
                $action = 'faqCategory';
                $add = 'addcat_send';
                break;
            default:
                $sendText = $this->utils->text('add_send');
                $action = 'faq';
                $add = 'add_send';
        }

        $listOptions = [
            'id' => self::ID,

            'title' => $this->utils->text('$add'),
            'base_href' => $scripturl . '?action=' . $action .';sa=add',
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
                        'function' => fn() => strtr($anchor, [
                            '{href}' => $scripturl . '?action=' . $action .';sa=edit',
                            '{title}' => $this->utils->text('edit'),
                        ]),
                    ],
                ],
                'delete' => [
                    'header' => [
                        'value' => $this->utils->text('delete'),
                    ],
                    'data' => [
                        'function' => fn() => strtr($anchor, [
                            '{href}' => $scripturl . '?action=' . $action .';sa=delete',
                            '{title}' => $this->utils->text('delete'),
                        ]),
                    ],
                ],
            ],
            'form' => array(
                'href' => $scripturl . '?action=' . $action .';sa=add',
            ),
            'additional_rows' => [
                [
                    'position' => 'top_of_list',
                    'value' => '
                    <input type="submit" name="'  . $action .'" value="' . $this->utils->text($add) . '" class="button">',
                ],
                [
                    'position' => 'bottom_of_list',
                    'value' => '<input type="submit" name="' . $action . '" value="' . $this->utils->text($add) . '" class="button">',
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