<?php

namespace SearchHub\Client\Plugin\QueryExpander;

use Generated\Shared\Transfer\SearchHubRequestTransfer;
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
        $searchHubClient = $this->getFactory()->getSearchHubClient();
        $searchHubRequestTransfer = new SearchHubRequestTransfer();
        $searchHubRequestTransfer->setUserQuery($searchQuery->getSearchString());
        $this->getLogger()->info("before optimize");
        $searchHubRequestTransfer = $searchHubClient->optimizeQuery($searchHubRequestTransfer);
        $optimizedQuery = $searchHubRequestTransfer->getSearchQuery();
        $this->getLogger()->info($searchQuery->getSearchString() . " -> " . $optimizedQuery);
        $searchQuery->setSearchString($optimizedQuery);
        $this->getLogger()->info("after optimize");
        return $searchQuery;
    }

}
