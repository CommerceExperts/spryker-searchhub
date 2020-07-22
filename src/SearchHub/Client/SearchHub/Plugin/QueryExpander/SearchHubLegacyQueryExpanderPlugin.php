<?php

namespace SearchHub\Client\SearchHub\Plugin\QueryExpander;

use SearchHub\Client\SearchHub\SearchHubRequest;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\Search\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\Search\Dependency\Plugin\QueryInterface;
use Spryker\Shared\Log\LoggerTrait;

class SearchHubLegacyQueryExpanderPlugin extends AbstractPlugin implements QueryExpanderPluginInterface {
	use LoggerTrait;

	/**
	 * @param QueryInterface $searchQuery
	 * @param array $requestParameters
	 *
	 * @return QueryInterface
	 */
	public function expandQuery(QueryInterface $searchQuery, array $requestParameters = []) {
		$searchHubClient = $this->getClient();
		$searchHubRequest = new SearchHubRequest(trim(strtolower($searchQuery->getSearchString())));
		$searchHubRequest = $searchHubClient->optimizeQuery($searchHubRequest);
		$optimizedQuery = $searchHubRequest->getSearchQuery();
		if ($searchQuery->getSearchString() !== $optimizedQuery) {
			$this->getLogger()->info("searchhub optimized query [" . $searchQuery->getSearchString() . "] -> [" . $optimizedQuery . "]");
		}
		$searchQuery->setSearchString($optimizedQuery);
		return $searchQuery;
	}
}
