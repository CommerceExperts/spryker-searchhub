<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SearchHubTest\Client;

use Codeception\Test\Unit;
use SearchHub\Client\SearchHubRequest;

/**
 * @group SearchHubTest
 */
class SearchHubTest extends Unit
{
    /**
     * @return void
     */
    public function testOptimizeQueryOnPrem()
    {
        if (!getenv('API_KEY')) {
            $this->markTestSkipped("cannot perform on prem tests without having a configured API-KEY in _bootstrap.php");
        }

        $factory = $this->factory();
        $searchHubRequest = $factory->getSearchHubClient(ConfigMock::FLAVOUR_ON_PREM)->optimizeQuery(new SearchHubRequest("notebokk"));
        $this->assertEquals("notebook", $searchHubRequest->getSearchQuery() );
    }

    /**
     * @return void
     */
    public function testOptimizeQueryCaching()
    {
        if (!getenv('API_KEY')) {
            $this->markTestSkipped("cannot perform on prem tests without having a configured API-KEY in _bootstrap.php");
        }

        $factory = $this->factory();

        //fill cache
        $start = microtime(true);
        $searchHubRequest = $factory->getSearchHubClient(ConfigMock::FLAVOUR_ON_PREM_UNCACHED)->optimizeQuery(new SearchHubRequest("notebokk"));
        $this->assertEquals($searchHubRequest->getSearchQuery(), "notebook");
        $durationInitial = microtime(true) - $start;

        //use cached data
        $start = microtime(true);
        $searchHubRequest = $factory->getSearchHubClient(ConfigMock::FLAVOUR_ON_PREM)->optimizeQuery(new SearchHubRequest("notebokk"));
        $this->assertEquals($searchHubRequest->getSearchQuery(), "notebook");
        $durationCached = microtime(true) - $start;

        //force cache flush
        $start = microtime(true);
        $searchHubRequest = $factory->getSearchHubClient(ConfigMock::FLAVOUR_ON_PREM_UNCACHED)->optimizeQuery(new SearchHubRequest("notebokk"));
        $this->assertEquals($searchHubRequest->getSearchQuery(), "notebook");
        $durationUncached = microtime(true) - $start;

        $this->assertGreaterThan( $durationCached * 10, $durationInitial, "weird. using cached entries is pretty slow (initial search vs. cache)");
        $this->assertGreaterThan( $durationCached * 10, $durationUncached, "weird. using cached entries is pretty slow (uncached search vs. cache)");
    }

    /**
     * @return void
     */
    public function testOptimizeQuerySaas()
    {
        $factory = $this->factory();
        $searchHubRequest = $factory->getSearchHubClient(ConfigMock::FLAVOUR_SAAS)->optimizeQuery(new SearchHubRequest("notebokk"));
        $this->assertEquals("notebook", $searchHubRequest->getSearchQuery());
    }

    /**
     * @return void
     */
    public function testOptimizeSuggestOnPrem()
    {
        if (!getenv('API_KEY')) {
            $this->markTestSkipped("cannot perform on prem tests without having a configured API-KEY in _bootstrap.php");
        }

        $factory = $this->factory();
        $searchHubRequest = $factory->getSearchHubClient(ConfigMock::FLAVOUR_ON_PREM)->optimizeSuggestQuery(new SearchHubRequest("notebokk"));
        $this->assertEquals("notebook", $searchHubRequest->getSearchQuery());
    }

    /**
     * @return void
     */
    public function testOptimizeSuggestSaas()
    {
        $this->markTestSkipped("apply to new SaaS model first");

        $factory = $this->factory();
        $searchHubRequest = $factory->getSearchHubClient(ConfigMock::FLAVOUR_SAAS)->optimizeSuggestQuery(new SearchHubRequest("notebokk"));

        $this->assertEquals(json_decode($searchHubRequest->getSearchQuery(), true)[0]["suggestions"][0], "notebook");
    }

    /**
     * @return SearchHubTestFactory
     */
    protected function factory(): SearchHubTestFactory
    {
        return new SearchHubTestFactory();
    }

}