<?php

declare(strict_types=1);

namespace SearchHub\Client;

use Generated\Shared\Transfer\SearchHubRequestTransfer;

/**
 * Interface SearchhubClientInterface
 *
 * @package SearchHub\Client
 */
interface SearchHubClientInterface
{
    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param SearchhubRequest $searchHubRequest
     *
     * @return SearchhubRequest
     */
    public function optimizeQuery(SearchHubRequest $searchHubRequest): SearchhubRequest;

    /**
     * Optimize Suggest Query by sending it to searchhub checking whether there is good suggestion
     *
     * @param SearchhubRequest $searchHubRequest
     *
     * @return SearchhubRequest
     */
    public function optimizeSuggestQuery(SearchHubRequest $searchHubRequest): SearchhubRequest;

}
