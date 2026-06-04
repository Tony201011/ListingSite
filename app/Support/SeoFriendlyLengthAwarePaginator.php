<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class SeoFriendlyLengthAwarePaginator extends LengthAwarePaginator
{
    public function __construct(
        $items,
        $total,
        $perPage,
        $currentPage = null,
        array $options = [],
        private readonly string $seoBaseUrl = '',
    ) {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }

    public static function fromPaginator(LengthAwarePaginatorContract $paginator, string $seoBaseUrl): self
    {
        return new self(
            $paginator->getCollection(),
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            $paginator->getOptions(),
            $seoBaseUrl,
        );
    }

    public function url($page): string
    {
        $page = max(1, (int) $page);
        $path = rtrim($this->seoBaseUrl !== '' ? $this->seoBaseUrl : $this->path(), '/');
        $url = $page === 1 ? $path : "{$path}/page/{$page}";

        $query = $this->query;
        unset($query[$this->pageName]);

        return empty($query) ? $url : $url.'?'.Arr::query($query);
    }
}
