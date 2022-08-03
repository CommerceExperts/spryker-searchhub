<?php

declare(strict_types=1);

namespace SearchHub\Client\SearchHub;

/**
 * Interface SearchHubClientInterface
 *
 * @package SearchHub\Client\SearchHub
 */
interface SearchHubClientInterface
{
    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param SearchHubRequest $searchHubRequest
     * @param bool $enableReporting
     *
     * @return SearchHubRequest
     */
    public function optimizeQuery(SearchHubRequest $searchHubRequest, bool $enableReporting = true): SearchHubRequest;

    /**
     * Optimize Suggest Query by sending it to searchhub checking whether there is good suggestion
     *
     * @param SearchHubRequest $searchHubRequest
     * @param bool $enableReporting
     *
     * @return SearchHubRequest
     */
    public function optimizeSuggestQuery(SearchHubRequest $searchHubRequest, bool $enableReporting = true): SearchHubRequest;

}
