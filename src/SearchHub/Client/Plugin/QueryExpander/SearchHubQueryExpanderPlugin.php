<?php

namespace SearchHub\Client\Plugin\QueryExpander;

use Generated\Shared\Transfer\SearchHubRequestTransfer;
use SearchHub\Client\SearchHubClient;
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
     * @throws \Spryker\Client\Kernel\Exception\Container\ContainerKeyNotFoundException
     *
     * @return QueryInterface
     */
    public function expandQuery(QueryInterface $searchQuery, array $requestParameters = [])
    {
        $searchHubClient = new SearchHubClient();
        $searchHubRequestTransfer = new SearchHubRequestTransfer();
        $searchHubRequestTransfer->setUserQuery($searchQuery->getSearchString());
        $searchHubRequestTransfer = $searchHubClient->optimizeQuery($searchHubRequestTransfer);
        $optimizedQuery = $searchHubRequestTransfer->getSearchQuery();
        if ($searchQuery->getSearchString() !== $optimizedQuery) {
            $this->getLogger()->info("searchhub optimized query [" . $searchQuery->getSearchString() . "] -> [" . $optimizedQuery . "]");
        }
        $searchQuery->setSearchString($optimizedQuery);
        return $searchQuery;
    }

}
