use SearchHub\Shared\SearchHubConstants;
use Twig\Cache\FilesystemCache;

// ---------- SearchHub
/*
 * in order to activate SearchHub query optimization
 * add 'new SearchHubQueryExpanderPlugin(),' in \Pyz\Client\Catalog\CatalogDependencyProvider::createCatalogSearchQueryExpanderPlugins
 * just before 'new PaginatedQueryExpanderPlugin(),'
 *
 * Configure the endpoint below to the value provided to you by CXP Commerce Experts (info@commerce-experts.com)
 */
$config[SearchHubConstants::SMARTQUERY_ENDPOINT] = 'https://test.searchhub.io/smartquery/v1/demo/spryker';

/*
* in order to activate SearchHub Suggest
* replace 'new CompletionResultFormatterPlugin(),' with 'new SearchHubSuggestResultFormatterPlugin(),' in \Pyz\Client\Catalog\CatalogDependencyProvider::createSuggestionResultFormatterPlugins
*
* Configure the endpoint below to the value provided to you by CXP Commerce Experts (info@commerce-experts.com)
*/
$config[SearchHubConstants::SMARTSUGGEST_ENDPOINT] = 'https://test.searchhub.io/smartsuggest/v1/demo/spryker';

$config[SearchHubConstants::REQUEST_TIMEOUT] = '1000';

$config[SearchHubConstants::USE_SAAS_MODE] = false;

//the following searchhub configuration items are only needed if USE_SAAS_MODE = false
$config[SearchHubConstants::API_KEY] = "request your api key from info@commerce-experts.com";
$config[SearchHubConstants::MAPPING_QUERIES_ENDPOINT] = "https://query.searchhub.io/mappingData?tenant=demo.spryker";
$config[SearchHubConstants::MAPPING_SUGGESTS_ENDPOINT] = "https://query.searchhub.io/suggest/data?tenant=demo.spryker";
$config[SearchHubConstants::MAPPING_LASTMODIFIED_ENDPOINT] = "https://query.searchhub.io/modificationTime?tenant=demo.spryker";
$config[SearchHubConstants::MAPPING_CACHE] = new FilesystemCache(sprintf('%s/data/cache/searchhub',APPLICATION_ROOT_DIR));
$config[SearchHubConstants::MAPPING_CACHE_TTL] = "600";
