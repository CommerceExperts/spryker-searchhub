<?php

namespace SearchHub\Client\Plugin\QueryExpander;

use Elastica\Query;
use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubFactory;
use SearchHub\Client\SearchHubRequest;
use SearchHubTest\Client\SearchHubTestFactory;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Log\LoggerTrait;

class SearchHubRankingQueryExpanderPlugin extends AbstractPlugin implements QueryExpanderPluginInterface
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
        $functionScore = new Query\FunctionScore();
        //$acerQuery = new Query\BoolQuery();
        //$acerQuery->addParam("brand", "acer");
        //$functionScore->addWeightFunction(20, $acerQuery);
        $functionScore->addFieldValueFactorFunction("integer-sort.price", -2.0);

        string-facet.facet-name
        $functionScore->setQuery($searchQuery->getSearchQuery()->getQuery());
        //$functionScore->setRandomScore(20);
        //$functionScore->setParams($elasticQuery->getParams());

        $searchQuery->getSearchQuery()->setQuery($functionScore);

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
