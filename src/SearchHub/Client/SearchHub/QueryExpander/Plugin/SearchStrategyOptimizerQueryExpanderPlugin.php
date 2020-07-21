<?php


namespace SearchHub\Client\SearchHub\Plugin\QueryExpander;

use Elastica\Query\MultiMatch;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
use Spryker\Shared\Log\LoggerTrait;

class SearchStrategyOptimizerQueryExpanderPlugin extends AbstractPlugin implements QueryExpanderPluginInterface
{
    use LoggerTrait;

    const MUST = 'must';
    const QUERY = 'query';
    const TYPE = 'type';

    /**
     * @param QueryInterface $searchQuery
     * @param array $requestParameters
     *
     * @return QueryInterface
     */
    public function expandQuery(QueryInterface $searchQuery, array $requestParameters = [])
    {
        $coreQuery = $searchQuery->getSearchQuery()->getParams(){self::QUERY}->getParams();
        $optimized = false;
        if (
            is_array($searchQuery->getSearchQuery()->getParams())
            && isset($searchQuery->getSearchQuery()->getParams(){self::QUERY})
            && isset($coreQuery[self::MUST])
            && is_array($coreQuery[self::MUST])) {

            $mustQueries = $coreQuery[self::MUST];
            for ($i = 0; $i < sizeof($mustQueries); $i++) {
                $param = $mustQueries[$i];
                if ($param instanceof MultiMatch && $param->hasParam(self::TYPE) && $param->getParam(self::TYPE) === MultiMatch::TYPE_CROSS_FIELDS) {
                    $param->setParam(self::TYPE, MultiMatch::TYPE_MOST_FIELDS);
                    $coreQuery[self::MUST][$i] = $param;
                    $optimized = true;
                    break;
                }
            }
            $searchQuery->getSearchQuery()->getParams(){self::QUERY}->setParams($coreQuery);
        }
        if (!$optimized) {
            $this->getLogger()->info("SearchStrategyOptimizerQueryExpanderPlugin failed to optimize search strategy. Maybe you have plugged me in too late in CatalogDependencyProvider?");
        }
        return $searchQuery;
    }
}
