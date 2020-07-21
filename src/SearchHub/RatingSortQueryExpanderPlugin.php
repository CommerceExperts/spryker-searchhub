<?php declare(strict_types=1);

namespace SearchHub\Client\Plugin\QueryExpander;

use Elastica\Query\FunctionScore;
use Generated\Shared\Search\PageIndexMap;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\Search\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\Search\Dependency\Plugin\QueryInterface;

/**
 * Class RatingSortQueryExpanderPlugin
 *
 * @package Pyz\Client\Search\Plugin\Elasticsearch\QueryExpander
 */
class RatingSortQueryExpanderPlugin extends AbstractPlugin implements QueryExpanderPluginInterface
{
    /**
     * @var string
     */
    public const ID_SORT_FIELD_NAME = 'search-result-data.id_product_abstract';
    /**
     * @var float
     */
    protected const ID_SORT_FIELD_VALUE_FACTOR = 0.001;
    /**
     * @var string
     */
    protected const RATING_SORT_FIELD_NAME = 'rating';
    /**
     * @var float
     */
    protected const RATING_SORT_FIELD_VALUE_FACTOR = 0.01;
    /**
     * @var integer
     */
    protected const SORT_FIELD_VALUE_MISSING = 1;
    /**
     * @var float
     */
    protected const SORT_WEIGHT = 1.0;

    /**
     * Specification:
     *
     * - Modifies the search query to a FunctionScore query, which gets the base query set as sub query
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.6/query-dsl-function-score-query.html#function-field-value-factor
     *
     * @param QueryInterface $searchQuery
     * @param array $requestParameters
     *
     * @return QueryInterface
     */
    public function expandQuery(QueryInterface $searchQuery, array $requestParameters = []): QueryInterface
    {
        $query = $searchQuery->getSearchQuery();
        $functionScoreQuery = new FunctionScore();
        $sortFieldName = sprintf(
            '%s.%s',
            PageIndexMap::INTEGER_SORT,
            static::RATING_SORT_FIELD_NAME
        );
        $functionScoreQuery->setQuery($query->getQuery());
        $functionScoreQuery->addWeightFunction(static::SORT_WEIGHT);
        $functionScoreQuery->addFieldValueFactorFunction(
            $sortFieldName,
            static::RATING_SORT_FIELD_VALUE_FACTOR,
            FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT,
            static::SORT_FIELD_VALUE_MISSING
        );
        $functionScoreQuery->addFieldValueFactorFunction(
            static::ID_SORT_FIELD_NAME,
            static::ID_SORT_FIELD_VALUE_FACTOR,
            FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT,
            static::SORT_FIELD_VALUE_MISSING
        );
        $functionScoreQuery->setScoreMode(FunctionScore::SCORE_MODE_SUM);
        $functionScoreQuery->setBoostMode(FunctionScore::BOOST_MODE_AVERAGE);
        $query->setQuery($functionScoreQuery);
        return $searchQuery;
    }
}
