<?php

namespace Faq\Entities;

class Faq extends Base
{
    public int $cat_id;
    public int $id;
    public string $log = '';
    public string $title = '';
    public string $body = '';

    public function getCatId(): int
    {
        return $this->cat_id;
    }

    public function setCatId(int $cat_id): void
    {
        $this->cat_id = $cat_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function setLog(string $log): void
    {
        $this->log = $log;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }
}