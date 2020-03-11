<?php

namespace SearchHub\Client\Plugin\QueryExpander;

use Generated\Shared\Search\PageIndexMap;
use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubRequest;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SearchElasticsearch\Plugin\QueryExpander\SuggestionByTypeQueryExpanderPlugin;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryExpanderPluginInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface;
use Spryker\Client\SearchElasticsearch\Plugin\ResultFormatter\CompletionResultFormatterPlugin;
use Spryker\Shared\Log\LoggerTrait;

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

        $searchHubClient = new SearchHubClient();
        $searchHubRequest = new SearchHubRequest();
        $searchHubRequest->setUserQuery(trim(strtolower($requestParameters["q"])));
        $searchHubRequest = $searchHubClient->optimizeSuggestQuery($searchHubRequest);

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
            $completions[] = $searchHubRequest->getSearchQuery();
        }
        return $completions;
    }

}
