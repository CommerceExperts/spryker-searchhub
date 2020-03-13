<?php

namespace SearchHub\Client;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Shared\Config\Config;

class SearchHubFactory extends AbstractFactory
{
    /**
     * @return SearchHubClientInterface
     */
    public function getSearchHubClient()
    {
        return new SearchHubClient(Config::getInstance());
    }
}
