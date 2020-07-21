<?php declare(strict_types=1);

namespace SearchHub\Client\Plugin\QueryExpander;

use Elastica\Param;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Generated\Shared\Search\PageIndexMap;
use InvalidArgumentException;
use Spryker\Client\Kernel\AbstractPlugin;
use
    Spryker\Client\Search\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\Search\Dependency\Plugin\QueryInterface;

/**
 * Class RankingQueryExpanderPlugin
 * @package Pyz\Client\Search\Plugin\Elasticsearch\QueryExpander
 */
class RankingQueryExpanderPlugin extends AbstractPlugin implements QueryExpanderPluginInterface
{
    /**
     * @param QueryInterface $searchQuery
     * @param array $requestParameters
     *
     * @return QueryInterface|void
     */
    public function expandQuery(QueryInterface $searchQuery, array $requestParameters = []): QueryInterface
    {
        $query = $searchQuery->getSearchQuery();
        $data = [
            'led' => [
                'value' => 1,
                'boost' => 100,
                'type' => 'boolean',
            ],
            PageIndexMap::INTEGER_SORT . "category:1" => [
                'worst' => 0,
                'best' => 50,
                'calc' => 'linear',
                'type' => 'range',
                'boost' => 1000,
            ],
            PageIndexMap::FULL_TEXT => [
                'type' => 'contains',
                'value' => [
                    '113_29885591',
                    '134_26145012'
                ],
                'boost' => 100,
                'boostType' => 'absolute',
            ],
        ];

        $query->setQuery($this->buildContainsBoost($query, PageIndexMap::FULL_TEXT, $data[PageIndexMap::FULL_TEXT]));

        $query->setQuery($this->buildRangeBoost($query, PageIndexMap::INTEGER_SORT . "category:1", $data[PageIndexMap::INTEGER_SORT . "category:1"]));

        return $searchQuery;
    }

    /**
     * @param $searchQuery
     * @param $data
     */
    protected function buildBooleanBoost(QueryInterface $searchQuery, array $data)
    {
        
    }

    /**
     *
     */
    protected function buildRangeBoost($searchQuery, $key, array $data)
    {

        return $searchQuery;

    }

    /**
     *
     */
    protected function buildContainsBoost($searchQuery, $key, array $data)
    {
        $query = $this->getBoolQuery($searchQuery);

        foreach ($data['value'] as $value) {
            $match = new Query\Match();
            $match->setFieldBoost($key, $data['boost'] ?? 1);
            $match->setFieldQuery($key, $value);

            $query->addShould($match);
        }

        return $query;
    }

    /**
     * @param Query $query
     *
     * @throws InvalidArgumentException
     *
     * @return BoolQuery
     */
    protected function getBoolQuery(Query $query): BoolQuery
    {
        $boolQuery = $query->getQuery();
        if (!$boolQuery instanceof BoolQuery) {
            throw new InvalidArgumentException(sprintf(
                'Ranking Query Expander available only with %s, got: %s',
                BoolQuery::class,
                get_class($boolQuery)
            ));
        }

        return $boolQuery;
    }
}
