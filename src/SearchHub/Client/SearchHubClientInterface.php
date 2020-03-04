<?php

declare(strict_types=1);

namespace Pyz\Client\SearchHub;

use Generated\Shared\Transfer\SearchHubRequestTransfer;

/**
 * Interface SearchhubClientInterface
 *
 * @package Pyz\Client\SearchHub
 */
interface SearchHubClientInterface
{
    /**
     * Optimze Query
     *
     * @param SearchhubRequestTransfer $searchHubRequestTransfer
     *
     * @return SearchhubRequestTransfer
     */
    public function optimizeQuery(SearchHubRequestTransfer $searchHubRequestTransfer): SearchhubRequestTransfer;
}
