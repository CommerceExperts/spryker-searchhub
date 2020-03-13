<?php


namespace SearchHubTest\Client;


use SearchHub\Shared\SearchHubConstants;
use Twig\Cache\FilesystemCache;

class ConfigMock
{

    public const FLAVOUR_ON_PREM = 'ON_PREM';
    public const FLAVOUR_ON_PREM_UNCACHED = 'ON_PREM_UNCACHED';
    public const FLAVOUR_SAAS = 'SAAS';

    private $config;

    public function __construct(string $flavour) {

        if ($flavour === static::FLAVOUR_ON_PREM || $flavour === static::FLAVOUR_ON_PREM_UNCACHED) {
            $this->config[SearchHubConstants::REQUEST_TIMEOUT] = '1000';
            $this->config[SearchHubConstants::USE_SAAS_MODE] = false;
            $this->config[SearchHubConstants::API_KEY] = getenv('API_KEY'); //add your API_KEY to _bootstrap.php or set it externally
            $this->config[SearchHubConstants::MAPPING_QUERIES_ENDPOINT] = "https://query.searchhub.io/mappingData?tenant=demo.spryker";
            $this->config[SearchHubConstants::MAPPING_SUGGESTS_ENDPOINT] = "https://query.searchhub.io/suggest/data?tenant=demo.spryker";
            $this->config[SearchHubConstants::MAPPING_LASTMODIFIED_ENDPOINT] = "https://query.searchhub.io/modificationTime?tenant=demo.spryker";
            $this->config[SearchHubConstants::MAPPING_CACHE] = new FilesystemCache('tests/_data/cache/searchhub');
            $this->config[SearchHubConstants::MAPPING_CACHE_TTL] = $flavour === static::FLAVOUR_ON_PREM_UNCACHED ? "-1" : "600";
        } elseif ($flavour === static::FLAVOUR_SAAS) {
            $this->config[SearchHubConstants::SMARTQUERY_ENDPOINT] = 'https://test.searchhub.io/smartquery/v1/demo/spryker';
            $this->config[SearchHubConstants::SMARTSUGGEST_ENDPOINT] = 'https://test.searchhub.io/smartsuggest/v1/demo/spryker';
            $this->config[SearchHubConstants::REQUEST_TIMEOUT] = '1000';
            $this->config[SearchHubConstants::USE_SAAS_MODE] = true;
        }
    }


    public function get(string $key) {
        return $this->config[$key];
    }
}