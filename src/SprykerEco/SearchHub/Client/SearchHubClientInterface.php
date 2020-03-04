<?php

declare(strict_types=1);

namespace SprykerEco\SearchHub\Client;

use Generated\Shared\Transfer\SearchHubRequestTransfer;

/**
 * Interface SearchhubClientInterface
 *
 * @package SprykerEco\SearchHub\Client
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
