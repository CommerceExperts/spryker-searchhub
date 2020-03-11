use SearchHub\Shared\SearchHubConstants;

// ---------- SearchHub
/*
 * in order to activate SearchHub query optimization
 * add 'new SearchHubQueryExpanderPlugin(),' in \Pyz\Client\Catalog\CatalogDependencyProvider::createCatalogSearchQueryExpanderPlugins
 * just before 'new PaginatedQueryExpanderPlugin(),'
 *
 * Configure the endpoint below to the value provided to you by CXP Commerce Experts (info@commerce-experts.com)
 */
$config[SearchHubConstants::SMARTQUERY_ENDPOINT] = 'https://test.searchhub.io/smartquery/v1/demo/spryker';

// replace 'new CompletionResultFormatterPlugin(),' with 'new SearchHubSuggestResultFormatterPlugin(),' in \Pyz\Client\Catalog\CatalogDependencyProvider::createSuggestionResultFormatterPlugins
/*
* in order to activate SearchHub Suggest
* replace 'new CompletionResultFormatterPlugin(),' with 'new SearchHubSuggestResultFormatterPlugin(),' in \Pyz\Client\Catalog\CatalogDependencyProvider::createSuggestionResultFormatterPlugins
*
* Configure the endpoint below to the value provided to you by CXP Commerce Experts (info@commerce-experts.com)
*/
$config[SearchHubConstants::SMARTSUGGEST_ENDPOINT] = 'https://test.searchhub.io/smartsuggest/v1/demo/spryker';

$config[SearchHubConstants::REQUEST_TIMEOUT] = '1000';
