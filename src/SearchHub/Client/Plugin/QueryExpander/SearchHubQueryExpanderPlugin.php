<?php

namespace SearchHub\Client\Plugin\QueryExpander;

use SearchHub\Client\SearchHubFactory;
use SearchHub\Client\SearchHubRequest;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
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
        $searchHubClient = $this->factory()->getSearchHubClient();
        $searchHubRequest = new SearchHubRequest(trim(strtolower($searchQuery->getSearchString())));
        $searchHubRequest = $searchHubClient->optimizeQuery($searchHubRequest);
        $optimizedQuery = $searchHubRequest->getSearchQuery();
        if ($searchQuery->getSearchString() !== $optimizedQuery) {
            $this->getLogger()->info("searchhub optimized query [" . $searchQuery->getSearchString() . "] -> [" . $optimizedQuery . "]");
        }
        $searchQuery->setSearchString($optimizedQuery);
        return $searchQuery;
    }

    /**
     * @return SearchHubFactory
     */
    protected function factory(): SearchHubFactory
    {
        return new SearchHubFactory();
    }

}
