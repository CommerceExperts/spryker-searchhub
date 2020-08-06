<?php

declare(strict_types=1);

namespace SearchHub\Client\SearchHub;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use SearchHub\Shared\SearchHub\SearchHubConstants;
use Spryker\Client\Kernel\AbstractClient;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Log\LoggerTrait;

/**
 * Class SearchhubClient
 * @package SearchHub\Client\SearchHub
 */
class SearchHubClient extends AbstractClient implements SearchHubClientInterface
{
    use LoggerTrait;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var Config
     */
    protected $config;

    public function __construct()
    {
        $this->config = Config::getInstance();
    }

    /**
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     * @throws Exception
     *
     */
    public function optimizeQuery(SearchHubRequest $searchHubRequest): SearchHubRequest
    {
        if (filter_var($this->config->get(SearchHubConstants::USE_SAAS_MODE), FILTER_VALIDATE_BOOLEAN)) {
            return $this->optimizeSaaS($searchHubRequest, false);
        } else {
            return $this->optimizeLocal($searchHubRequest, false);
        }
    }

    /**0
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     * @throws Exception
     *
     */
    public function optimizeSuggestQuery(SearchHubRequest $searchHubRequest): SearchHubRequest
    {
        if (filter_var($this->config->get(SearchHubConstants::USE_SAAS_MODE), FILTER_VALIDATE_BOOLEAN)) {
            return $this->optimizeSaaS($searchHubRequest, true);
        } else {
            return $this->optimizeLocal($searchHubRequest, true);
        }
    }

    protected function optimizeSaaS(SearchHubRequest $searchHubRequest, bool $isSuggest)
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

    protected function optimizeLocal(SearchHubRequest $searchHubRequest, bool $isSuggest)
    {
        $mappings = $this->loadMappings($this->config->get($isSuggest ? SearchHubConstants::MAPPING_SUGGESTS_ENDPOINT : SearchHubConstants::MAPPING_QUERIES_ENDPOINT ));
        if (isset($mappings[$searchHubRequest->getUserQuery()]) ) {
            $mapping = $mappings[$searchHubRequest->getUserQuery()];
            if (is_array($mapping)) {
                if (isset($mapping["redirect"])) {
                    if (strpos($mapping["redirect"], 'http') === 0) {
                        header('Location: ' . $mapping["redirect"]);
                    }
                    else {
                        header('Location: ' . $this->config->get(SearchHubConstants::REDIRECTS_BASE_URL ) . $mapping["redirect"]);
                    }
                    exit;
                }
                else {
                    //v2
                    $searchHubRequest->setSearchQuery($mapping["masterQuery"]);
                }
            } else {
                //v1
                $searchHubRequest->setSearchQuery($mapping);
            }
            return $searchHubRequest;
        }
        //downwards compatibility for suggest api
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
                'timeout' => (float) $this->config->get(SearchHubConstants::REQUEST_TIMEOUT, 0.01),
            ]);
        }
        return $this->httpClient;
    }

    /**
     * Get Request Uri for suggest or default search (SaaS mode)
     *
     * @throws Exception
     *
     * @param string $userQuery
     *
     * @return string
     */
    protected function getRequestUri(string $userQuery, bool $isSuggest): string
    {
        $endpoint = $this->config->get($isSuggest ? SearchHubConstants::SMARTSUGGEST_ENDPOINT : SearchHubConstants::SMARTQUERY_ENDPOINT);
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
    protected function loadMappings(string $uri): array
    {
        $cache = $this->config->get(SearchHubConstants::MAPPING_CACHE);
        $key = $cache->generateKey("SearchHubClient", $uri);

        $mappings = $this->loadMappingsFromCache($key);
        if ($mappings === null ) {
            try {
                $mappingsResponse = $this->getHttpClient()->get($uri, ['headers' => ['apikey' => $this->config->get(SearchHubConstants::API_KEY)]]);
                assert($mappingsResponse instanceof Response);
                $indexedMappings = $this->indexMappings(json_decode($mappingsResponse->getBody()->getContents(), true));
                $cache->write($key, json_encode($indexedMappings));
                return $indexedMappings;
            } catch (Exception $e) {
                $this->getLogger()->error($e->getMessage());
                return array();
            }
        }
        return json_decode($mappings, true);
    }

    protected function loadMappingsFromCache(string $cacheFile)
    {
        if (file_exists($cacheFile) ) {
            if (time() - filemtime($cacheFile) < $this->config->get(SearchHubConstants::MAPPING_CACHE_TTL)) {
                return file_get_contents($cacheFile);
            } else {
                $lastModifiedResponse = $this->getHttpClient()->get($this->config->get(SearchHubConstants::MAPPING_LASTMODIFIED_ENDPOINT), ['headers' => ['apikey' => $this->config->get(SearchHubConstants::API_KEY)]]);
                assert($lastModifiedResponse instanceof Response);
                if (filemtime($cacheFile) > ((int)($lastModifiedResponse->getBody()->getContents()) / 1000 + $this->config->get(SearchHubConstants::MAPPING_CACHE_TTL))) {
                    touch($cacheFile);
                    return file_get_contents($cacheFile);
                }
            }
        }
        return null;
    }

    /**
     * @param $mappingsRaw
     * @return array
     */
    protected function indexMappings($mappingsRaw): array
    {
        $indexedMappings = array();
        if (isset($mappingsRaw["mappings"]) && is_array($mappingsRaw["mappings"])) { // v1
            foreach ($mappingsRaw["mappings"] as $mapping) {
                $indexedMappings[$mapping["from"]] = $mapping["to"];
            }
        }
        else if (isset($mappingsRaw["clusters"]) && is_array($mappingsRaw["clusters"])) { //v2
            foreach ($mappingsRaw["clusters"] as $mapping) {
                foreach ($mapping["queries"] as $variant) {
                    $indexedMappings[$variant] = array();
                    $indexedMappings[$variant]["masterQuery"] = $mapping["masterQuery"];
                    if ($mapping["redirect"] !== null) {
                        $indexedMappings[$variant]["redirect"] = $mapping["redirect"];
                    }
                }
            }
        } else if (isset($mappingsRaw["suggestions"]) && is_array($mappingsRaw["suggestions"])) { // suggest
            foreach ($mappingsRaw["suggestions"] as $suggestion) {
                foreach ($suggestion["variants"] as $variant) {
                    $indexedMappings[$variant] = $suggestion["bestQuery"];
                }
            }
        }
        return $indexedMappings;
    }
}
