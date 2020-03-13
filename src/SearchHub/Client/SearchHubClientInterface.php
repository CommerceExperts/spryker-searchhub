<?php

declare(strict_types=1);

namespace SearchHub\Client;

/**
 * Interface SearchHubClientInterface
 *
 * @package SearchHub\Client
 */
interface SearchHubClientInterface
{
    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     */
    public function optimizeQuery(SearchHubRequest $searchHubRequest): SearchHubRequest;

    /**
     * Optimize Suggest Query by sending it to searchhub checking whether there is good suggestion
     *
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     */
    public function optimizeSuggestQuery(SearchHubRequest $searchHubRequest): SearchHubRequest;

}
