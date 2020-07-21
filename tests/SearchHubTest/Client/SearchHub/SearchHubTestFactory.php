<?php


namespace SearchHubTest\Client\SearchHub;


use SearchHub\Client\SearchHub\SearchHubClient;
use SearchHub\Client\SearchHub\SearchHubClientInterface;

class SearchHubTestFactory
{
    /**
     * @param string $flavour
     * @return SearchHubClientInterface
     */
    public function getSearchHubClient(string $flavour)
    {
        return new SearchHubClient(new ConfigMock($flavour));
    }

}