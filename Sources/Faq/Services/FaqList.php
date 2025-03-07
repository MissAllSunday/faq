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
        $maxIndex = $this->repository->count();
        $start = $this->start;

        $listOptions = [
            'id' => self::ID,
            'title' => 'replace by type title',
            'base_href' => 'todo',
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
                        'function' => fn() => strtr('some text string for editing', [
                            'href' => 'href by type',
                            'title' => 'title/name bt type',
                        ]),
                    ],
                ],
                'delete' => [
                    'header' => [
                        'value' => $this->utils->text('delete'),
                    ],
                    'data' => [
                        'function' => fn() => strtr('some text string for deleting', [
                            'href' => 'href by type',
                            'title' => 'title/name bt type',
                        ]),
                    ],
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