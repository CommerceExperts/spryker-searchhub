<?php

namespace SearchHub\Client\SearchHub\Plugin\QueryExpander;

use SearchHub\Client\SearchHub\SearchHubRequest;
use Spryker\Client\SearchElasticsearch\Plugin\ResultFormatter\CompletionResultFormatterPlugin;
use Spryker\Shared\Log\LoggerTrait;

/*
 * requires and supports spryker module search/elasticsearch
 */
class SearchHubSuggestResultFormatterPlugin extends CompletionResultFormatterPlugin
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

        $searchHubRequest = $this->getClient()
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
}
