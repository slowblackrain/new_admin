<?php

namespace App\Contracts;

interface SearchIntentionInterface
{
    /**
     * Analyze the user query and return structured data.
     *
     * @param string $query
     * @return array
     * [
     *    'keywords' => ['term1', 'term2'],
     *    'filters' => [
     *        'price_min' => 1000,
     *        'price_max' => 50000,
     *        'color' => 'red'
     *    ],
     *    'sort' => 'price_asc'
     * ]
     */
    public function analyze(string $query): array;
}
