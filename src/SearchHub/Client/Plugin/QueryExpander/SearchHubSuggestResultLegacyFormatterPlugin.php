<?php

namespace SearchHub\Client\Plugin\QueryExpander;

use SearchHub\Client\SearchHubFactory;
use SearchHub\Client\SearchHubRequest;
use Spryker\Client\Search\Plugin\Elasticsearch\ResultFormatter\CompletionResultFormatterPlugin;
use Spryker\Shared\Log\LoggerTrait;

/*
 * supports legacy spryker module spryker/search <= 8.9.0, when spryker/search-elasticsearch is not available
 */
class SearchHubSuggestResultLegacyFormatterPlugin extends CompletionResultFormatterPlugin
{

    use LoggerTrait;

    const BEST_MATCHES = "best matches";

    /**
     * @param mixed $searchResult
     * @param array $requestParameters
     *
     * @return mixed
     */
    public function formatResult($searchResult, array $requestParameters = [])
    {
        $completions = parent::formatResult($searchResult, $requestParameters);

        return $this->getSearchHubSuggestions($requestParameters, $completions);
    }

    /**
     * @param array $requestParameters
     * @param $completions
     * @return array
     * @throws \Exception
     */
    public function getSearchHubSuggestions(array $requestParameters, $completions): array
    {
        if (!isset($requestParameters["q"])) {
            return $completions;
        }

        $searchHubRequest = $this->factory()->getSearchHubClient()
            ->optimizeSuggestQuery(new SearchHubRequest(trim(strtolower($requestParameters["q"]))));

        if ($searchHubRequest->getIsException()) {
            return $completions;
        }

        $suggestResultJson = json_decode($searchHubRequest->getSearchQuery(), true);
        if (is_array($suggestResultJson)) {
            foreach ($suggestResultJson as $json) {
                if ($json["name"] === self::BEST_MATCHES && is_array($json["suggestions"])) {
                    foreach ($json["suggestions"] as $query) {
                        $completions[] = $query;
                    }
                }
            }
        } else {
            //downwards compatibility. Will be removed in future.
            $completions[] = $searchHubRequest->getSearchQuery();
        }
        return $completions;
    }

    /**
     * @return SearchHubFactory
     */
    protected function factory(): SearchHubFactory
    {
        return new SearchHubFactory();
    }

}
