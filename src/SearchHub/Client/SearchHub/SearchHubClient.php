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
use Spryker\Shared\Kernel\Store;
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

    /**
     * @var bool
     */
    protected $isReportingEnabled;

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
    public function optimizeQuery(SearchHubRequest $searchHubRequest, bool $enableReporting = true): SearchHubRequest
    {
        $this->isReportingEnabled = $enableReporting;
        
        try {
            if (filter_var($this->config->get(SearchHubConstants::USE_SAAS_MODE), FILTER_VALIDATE_BOOLEAN)) {
                return $this->optimizeSaaS($searchHubRequest, false);
            } else {
                return $this->optimizeLocal($searchHubRequest, false);
            }
        } catch (\Exception $e) {
            $searchHubRequest->setSearchQuery($searchHubRequest->getUserQuery());
            return $searchHubRequest;
        }
    }

    /**
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     * @throws Exception
     *
     */
    public function optimizeSuggestQuery(SearchHubRequest $searchHubRequest, bool $enableReporting = true): SearchHubRequest
    {
        $this->isReportingEnabled = $enableReporting;

        try {
            if (filter_var($this->config->get(SearchHubConstants::USE_SAAS_MODE), FILTER_VALIDATE_BOOLEAN)) {
                return $this->optimizeSaaS($searchHubRequest, true);
            } else {
                return $this->optimizeLocal($searchHubRequest, true);
            }
        } catch (\Exception $e) {
            $searchHubRequest->setSearchQuery($searchHubRequest->getUserQuery());
            return $searchHubRequest;
        }
    }

    protected function optimizeSaaS(SearchHubRequest $searchHubRequest, bool $isSuggest)
    {
        $client = $this->getHttpClient((float)$this->config->get(SearchHubConstants::REQUEST_TIMEOUT, 0.01));
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
        $startTimestamp = microtime(true);

        $mappings = $this->loadMappings($this->config->get($isSuggest ? SearchHubConstants::MAPPING_SUGGESTS_ENDPOINT : SearchHubConstants::MAPPING_QUERIES_ENDPOINT ));
        if (isset($mappings[$searchHubRequest->getUserQuery()]) ) {
            $mapping = $mappings[$searchHubRequest->getUserQuery()];
            if (is_array($mapping)) {
                if (isset($mapping["redirect"])) {
                    $redirectUrl = $mapping["redirect"];
                    if (strpos($mapping["redirect"], 'http') === false) {
                        $redirectUrl = $this->config->get(SearchHubConstants::REDIRECTS_BASE_URL ) . $mapping["redirect"];
                    }
                    header('Location: ' . $redirectUrl);
                    
                    $this->report(
                        $searchHubRequest->getUserQuery(),
                        $mapping["masterQuery"],
                        microtime(true) - $startTimestamp,
                        $redirectUrl
                    );
                    exit;
                }
                else {
                    //v2
                    $searchHubRequest->setSearchQuery($mapping["masterQuery"]);
                    $this->report(
                        $searchHubRequest->getUserQuery(),
                        $mapping["masterQuery"],
                        microtime(true) - $startTimestamp,
                        null
                    );
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
     * @param string $originalSearchString
     * @param string $optimizedSearchString
     * @param float $duration
     * @param string $redirect
     *
     * @return void
     */
    protected function report(
        string $originalSearchString,
        string $optimizedSearchString,
        float $duration,
        string $redirect
    ): void {
        if (!$this->isReportingEnabled) {
            return;
        }
        
        $event = sprintf(
            '[
                {
                    "from": "%s",
                    "to": "%s",
                    "redirect": %s,
                    "durationNs": %d,
                    "tenant": {
                        "name": "%s",
                        "channel": "%s"
                    },
                    "queryMapperType": "SimpleQueryMapper",
                    "statsType": "mappingStats",
                    "libVersion": "unknown"
                }
            ]',
            $originalSearchString,
            $optimizedSearchString,
            $redirect == null ? "null" : "\"$redirect\"",
            $duration * 1000 * 1000 * 1000,
            $this->config->get(SearchHubConstants::ACCOUNT_NAME),
            strtolower(Store::getInstance()->getStoreName())
        );

        $promise = $this->getHttpClient((float) 0.01)->requestAsync(
            'post',
            'https://import.searchhub.io/reportStats',
            [
                'headers' => [
                    'apikey' => $this->config->get(SearchHubConstants::API_KEY),
                    'X-Consumer-Username' => $this->config->get(SearchHubConstants::ACCOUNT_NAME),
                    'Content-type' => 'application/json',
                ],
                'body' => $event,
            ]
        );
        try {
            $promise->wait();
        } catch (\Exception $ex) {
             /*
              * will throw a timeout exception which we ignore, as we don't want to wait for any result
              */
            $this->logAfterReporting($originalSearchString, $optimizedSearchString, $duration, $redirect);
        }
    }

    /**
     * This method is intended to be extended on PYZ / Project Level
     * Add any form of logging you want or keep it empty
     *
     * @param string $originalSearchString
     * @param string $optimizedSearchString
     * @param float $duration
     * @param bool $redirect
     * 
     * @return void
     */
    protected function logAfterReporting(
        string $originalSearchString,
        string $optimizedSearchString,
        float $duration,
        bool $redirect
    ): void
    {
        // Put your logging here
    }

    /**
     * Get Http Client
     *
     * @throws Exception
     *
     * @return ClientInterface
     */
    protected function getHttpClient(float $timeout): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => $timeout,
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
                $mappingsResponse = $this->getHttpClient((float)$this->config->get(SearchHubConstants::REQUEST_TIMEOUT, 0.01))->get($uri, ['headers' => ['apikey' => $this->config->get(SearchHubConstants::API_KEY)]]);
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
                $lastModifiedResponse = $this->getHttpClient((float)$this->config->get(SearchHubConstants::REQUEST_TIMEOUT, 0.01))->get($this->config->get(SearchHubConstants::MAPPING_LASTMODIFIED_ENDPOINT), ['headers' => ['apikey' => $this->config->get(SearchHubConstants::API_KEY)]]);
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
