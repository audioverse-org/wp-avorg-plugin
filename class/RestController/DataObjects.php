<?php

namespace Avorg\RestController;

use Avorg\DataObjectRepository;
use Avorg\DataObjectRepository\BookRepository;
use Avorg\RestController;
use Avorg\WordPress;
use function defined;

if (!defined('ABSPATH')) exit;

abstract class DataObjects extends RestController
{
    protected $args = [
        'search' => [
            'description' => 'Search term',
            'type' => 'string'
        ],
        'start' => [
            'description' => 'Index of item in result set that should begin returned data',
            'type' => 'integer'
        ]
    ];

    /** @var DataObjectRepository $repository */
    protected $repository;

    public function getData($request = null)
    {
        $search = $request['search'] ?? null;
        $start = $request['start'] ?? null;

        return $this->repository->getDataObjects($search, $start);
    }
}