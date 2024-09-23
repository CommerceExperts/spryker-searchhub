use SearchHub\Shared\SearchHub\SearchHubConstants;
use Twig\Cache\FilesystemCache;

// ---------- SearchHub
/*

* add "SearchHub" to your $config[KernelConstants::PROJECT_NAMESPACES]
*
* add 'new SearchHubQueryExpanderPlugin(),' in \Pyz\Client\Catalog\CatalogDependencyProvider::createCatalogSearchQueryExpanderPlugins
* just before 'new PaginatedQueryExpanderPlugin(),'
* to activate query optimization for SEARCH results
*
* replace 'new CompletionResultFormatterPlugin(),' with 'new SearchHubSuggestResultFormatterPlugin(),'
* in \Pyz\Client\Catalog\CatalogDependencyProvider::createSuggestionResultFormatterPlugins
* to activate optimized SUGGEST results
*
* add 'new SearchStrategyOptimizerQueryExpanderPlugin(),' in \Pyz\Client\Catalog\CatalogDependencyProvider::createCatalogSearchQueryExpanderPlugins
* just after 'new SearchHubQueryExpanderPlugin(),'
* to optimize the search strategy used by Spryker inside ElasticSearch
*/

$config[SearchHubConstants::ACCOUNT_NAME] = "cxp";

$config[SearchHubConstants::API_KEY] = "request your api key from info@commerce-experts.com";

$config[SearchHubConstants::MAPPING_QUERIES_ENDPOINT] = sprintf('https://query.searchhub.io/mappingData/v2?tenant=%s.%s', $config[SearchHubConstants::ACCOUNT_NAME], strtolower(Store::getInstance()->getStoreName()));
$config[SearchHubConstants::MAPPING_SUGGESTS_ENDPOINT] = sprintf('https://query.searchhub.io/suggest/data?tenant=%s.%s', $config[SearchHubConstants::ACCOUNT_NAME], strtolower(Store::getInstance()->getStoreName()));
$config[SearchHubConstants::MAPPING_LASTMODIFIED_ENDPOINT] = sprintf('https://query.searchhub.io/modificationTime?tenant=%s.%s', $config[SearchHubConstants::ACCOUNT_NAME], strtolower(Store::getInstance()->getStoreName()));
$config[SearchHubConstants::MAPPING_CACHE] = new FilesystemCache(sprintf('%s/data/cache/searchhub/%s',APPLICATION_ROOT_DIR, Store::getInstance()->getStoreName()));
$config[SearchHubConstants::MAPPING_CACHE_TTL] = "600";
$config[SearchHubConstants::REQUEST_TIMEOUT] = '1000';

/*
* if you don't have an API key yet, you may use the SearchHub SaaS test account
* by setting $config[SearchHubConstants::USE_SAAS_MODE]=true
*/
$config[SearchHubConstants::USE_SAAS_MODE] = false;
$config[SearchHubConstants::SMARTQUERY_ENDPOINT] = 'https://test.searchhub.io/smartquery/v1/cxp.demo';
$config[SearchHubConstants::USE_SUGGEST_SAAS_MODE] = false;
$config[SearchHubConstants::SMARTSUGGEST_ENDPOINT] = 'https://test.searchhub.io/smartsuggest/v1/cxp.demo';

/*
* optional. Can be used if you want to use
* a) relative redirect URLs
* b) different stages (dev, int, prod)
*/
$config[SearchHubConstants::REDIRECTS_BASE_URL] = 'https://www.my-shop.com';
