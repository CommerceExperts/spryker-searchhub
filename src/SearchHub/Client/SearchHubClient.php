<?php

declare(strict_types=1);

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use SearchHub\Shared\SearchHubConstants;
use Spryker\Client\Kernel\AbstractClient;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Log\LoggerTrait;
use Twig\Cache\FilesystemCache;

/**
 * Class SearchhubClient
 * @package SearchHub\Client
 */
class SearchHubClient extends AbstractClient implements SearchHubClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    use LoggerTrait;

    /**
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     * @throws Exception
     *
     */
    public function optimizeQuery(SearchHubRequest $searchHubRequest): SearchHubRequest
    {
        if (Config::get(SearchHubConstants::USE_SAAS_MODE)) {
            return $this->optimize($searchHubRequest, false);
        } else {
            return $this->optimizeLocal($searchHubRequest, false);
        }
    }

    /**
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     * @throws Exception
     *
     */
    public function optimizeSuggestQuery(SearchHubRequest $searchHubRequest): SearchHubRequest
    {
        if (Config::get(SearchHubConstants::USE_SAAS_MODE)) {
            return $this->optimize($searchHubRequest, true);
        } else {
            return $this->optimizeLocal($searchHubRequest, true);
        }
    }

    private function optimize(SearchHubRequest $searchHubRequest, bool $isSuggest)
    {
        $client = $this->getHttpClient();
        $uri = $this->getRequestUri($searchHubRequest->getUserQuery(), $isSuggest);
        try {
            $optimizedQuery = $client->get($uri);
            assert($optimizedQuery instanceof Response);
            $searchHubRequest->setSearchQuery($optimizedQuery->getBody()->getContents());
            $searchHubRequest->setIsException(false);
        } catch (Exception $e) {
            $searchHubRequest->setSearchQuery($searchHubRequest->getUserQuery());
            $searchHubRequest->setIsException(true);
            $searchHubRequest->setExceptionMessage($e->getMessage());
            $this->getLogger()->error($e->getMessage());
        }
        return $searchHubRequest;

    }

    private function optimizeLocal(SearchHubRequest $searchHubRequest, bool $isSuggest)
    {
        $mappings = $this->loadMappings(Config::get($isSuggest ? SearchHubConstants::MAPPING_SUGGESTS_ENDPOINT : SearchHubConstants::MAPPING_QUERIES_ENDPOINT ));
        if (isset($mappings[$searchHubRequest->getUserQuery()]) ) {
            $searchHubRequest->setSearchQuery($mappings[$searchHubRequest->getUserQuery()]);
            return $searchHubRequest;
        }
        $searchHubRequest->setSearchQuery($searchHubRequest->getUserQuery());

        return $searchHubRequest;

    }

    /**
     * Get Http Client
     *
     * @throws Exception
     *
     * @return ClientInterface
     */
    protected function getHttpClient(): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float) Config::get(SearchHubConstants::REQUEST_TIMEOUT, 0.01),
            ]);
        }
        return $this->httpClient;
    }

    /**
     * Get Request Uri for suggest or default search
     *
     * @throws Exception
     *
     * @param string $userQuery
     *
     * @return string
     */
    protected function getRequestUri(string $userQuery, bool $isSuggest): string
    {
        $endpoint = Config::get($isSuggest ? SearchHubConstants::SMARTSUGGEST_ENDPOINT : SearchHubConstants::SMARTQUERY_ENDPOINT);
        return $endpoint . '?' . http_build_query(
                ['userQuery' => $userQuery],
                '',
                '&'
            );
    }

    /**
     * @return array
     * @throws Exception
     */
    private function loadMappings(string $uri): array
    {
        $cache = Config::get(SearchHubConstants::MAPPING_CACHE);
        $key = $cache->generateKey("SearchHubClient", $uri);

        $mappings = $this->loadMappingsFromCache($key);
        if ($mappings === null ) {
            try {
                $client = $this->getHttpClient();
                $mappingsResponse = $client->get($uri, [
                    'headers' => ['apikey' => Config::get(SearchHubConstants::API_KEY)]
                ]);
                assert($mappingsResponse instanceof Response);
                $mappingsRaw = json_decode($mappingsResponse->getBody()->getContents(), true);
                $indexedMappings = array();
                if (isset($mappingsRaw["mappings"]) && is_array($mappingsRaw["mappings"])) {
                    foreach ($mappingsRaw["mappings"] as $mapping) {
                        $indexedMappings[$mapping["from"]] = $mapping["to"];
                    }
                } else if (isset($mappingsRaw["suggestions"]) && is_array($mappingsRaw["suggestions"])) {
                    foreach ($mappingsRaw["suggestions"] as $suggestion) {
                        foreach ($suggestion["variants"] as $variant) {
                            $indexedMappings[$variant] = $suggestion["bestQuery"];
                        }
                    }
                }
                $cache->write($key, json_encode($indexedMappings));
                return $indexedMappings;
            } catch (Exception $e) {
                $this->getLogger()->error($e->getMessage());
                return array();
            }
        }
        return json_decode($mappings, true);
    }

    private function loadMappingsFromCache(string $key)
    {
        if (file_exists($key) && time() - filemtime($key) < Config::get(SearchHubConstants::MAPPING_CACHE_TTL) ) {
            return file_get_contents($key);
        }
        return null;
    }
}
