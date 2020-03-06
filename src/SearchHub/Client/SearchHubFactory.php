<?php

namespace SearchHub\Client;

use Spryker\Client\Catalog\CatalogFactory as SprykerCatalogFactory;
use Spryker\Client\Kernel\AbstractFactory;

class SearchHubFactory extends AbstractFactory
{
    const CLIENT_SEARCHHUB = 'searchhub client';

    /**
     * @throws \Spryker\Client\Kernel\Exception\Container\ContainerKeyNotFoundException

     * @return \SearchHub\Client\SearchHubClientInterface
     */
    public function getSearchHubClient()
    {
        return new SearchHubClient();
    }
}
