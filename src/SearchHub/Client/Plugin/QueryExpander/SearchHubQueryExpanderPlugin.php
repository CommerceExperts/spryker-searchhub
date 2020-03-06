<?php

namespace SearchHub\Client\Plugin\QueryExpander;

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubRequest;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\Search\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\Search\Dependency\Plugin\QueryInterface;
use Spryker\Shared\Log\LoggerTrait;

class SearchHubQueryExpanderPlugin extends AbstractPlugin implements QueryExpanderPluginInterface
{

    use LoggerTrait;

    /**
     * @param QueryInterface $searchQuery
     * @param array $requestParameters
     *
     * @return QueryInterface
     */
    public function expandQuery(QueryInterface $searchQuery, array $requestParameters = [])
    {
        $searchHubClient = new SearchHubClient();
        $searchHubRequest = new SearchHubRequest();
        $searchHubRequest->setUserQuery($searchQuery->getSearchString());
        $searchHubRequest = $searchHubClient->optimizeQuery($searchHubRequest);
        $optimizedQuery = $searchHubRequest->getSearchQuery();
        if ($searchQuery->getSearchString() !== $optimizedQuery) {
            $this->getLogger()->info("searchhub optimized query [" . $searchQuery->getSearchString() . "] -> [" . $optimizedQuery . "]");
        }
        $searchQuery->setSearchString($optimizedQuery);
        return $searchQuery;
    }

}
