<?php

namespace Faq\Services;

use Faq\Entities\FaqEntity;
use Faq\FaqAdmin;
use Faq\FaqConfig;
use Faq\Repositories\CategoryRepository;
use Faq\Repositories\FaqRepository;

class FaqService
{
    protected CategoryRepository $categoryRepository;
    protected FaqRepository $faqRepository;
    protected FaqConfig $config;

    public function __construct(
        CategoryRepository $categoryRepository = null,
        FaqRepository $faqRepository = null,
        FaqConfig $config = null
    )
    {
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
        $this->faqRepository = $faqRepository ?? new FaqRepository();
        $this->config = $config ?? new FaqConfig();
    }

    public function getAll(int $start = 0): array
    {
        $limit = $this->config->setting(FaqAdmin::SETTINGS_PAGINATION, 0);
        $data = $this->faqRepository->getAll($start, $limit);
        $data['pagination'] = $this->buildPagination($start, $data['total']);

        return $data;
    }

    public function getAllCategories(int $start = 0): array
    {
        $limit = $this->config->setting(FaqAdmin::SETTINGS_PAGINATION, 0);

        return $this->categoryRepository->getAll($start, $limit)['entities'];
    }

    public function getById(int $id = 0): FaqEntity
    {
        // No entity? send a brand new one
        if (!$id) {
            return $this->faqRepository->getEntity();
        }  else {
            return $this->faqRepository->getById($id);
        }
    }

    public function searchBy(string $searchValue, int $start = 0): array
    {
        $limit = $this->config->setting(FaqAdmin::SETTINGS_PAGINATION, 0);
        $data = $this->faqRepository->searchBy($searchValue, $start, $limit);
        $data['pagination'] = $this->buildPagination($start, $data['total']);

        return $data;

    }

    public function buildPagination(int $start, int $total = 0, string $paginationUrl = null): string
    {
        global $context;

        $paginationUrl = $paginationUrl ?? $context['current_url'];
        $limit = $this->config->setting(FaqAdmin::SETTINGS_PAGINATION);

        if (empty($limit) || !$total) {
            return '';
        }

        return constructPageIndex(
            $paginationUrl,
            $start,
            $total,
            $limit
        );
    }

    public function getLatest(): array
    {
        $limit = $this->config->setting(FaqAdmin::SETTINGS_SHOW_LATEST, 0);

        return ($limit > 1) ? $this->faqRepository->getLatest($limit) : [];
    }
}
