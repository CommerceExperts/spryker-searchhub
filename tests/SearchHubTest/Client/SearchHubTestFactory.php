<?php


namespace SearchHubTest\Client;


use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubClientInterface;

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