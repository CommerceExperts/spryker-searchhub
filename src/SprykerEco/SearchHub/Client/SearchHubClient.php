<?php

declare(strict_types=1);

namespace SprykerEco\SearchHub\Client;

use Exception;
use Generated\Shared\Transfer\SearchhubRequestTransfer;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use SprykerEco\SearchHub\Shared\SearchHubConstants;
use Spryker\Client\Kernel\AbstractClient;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Log\LoggerTrait;

/**
 * Class SearchhubClient
 * @package SprykerEco\SearchHub\Client
 */
class SearchHubClient extends AbstractClient implements SearchHubClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    use LoggerTrait;

    /**
     * Optimize Query
     *
     * @param SearchHubRequestTransfer $searchHubRequestTransfer
     *
     * @return SearchhubRequestTransfer
     *@throws Exception
     *
     */
    public function optimizeQuery(SearchhubRequestTransfer $searchHubRequestTransfer): SearchhubRequestTransfer
    {
        $client = $this->getHttpClient();
        $uri = $this->getRequestUri($searchHubRequestTransfer->getUserQuery());
        try {
            $optimizedQuery = $client->get($uri);
            assert($optimizedQuery instanceof Response);
            $searchHubRequestTransfer->setSearchQuery($optimizedQuery->getBody()->getContents());
            $this->getLogger()->info($optimizedQuery->getStatusCode() . ": " . $optimizedQuery->getBody()->getContents());
            $searchHubRequestTransfer->setIsException(false);
        } catch (Exception $e) {
            $searchHubRequestTransfer->setSearchQuery($searchHubRequestTransfer->getUserQuery());
            $searchHubRequestTransfer->setIsException(true);
            $searchHubRequestTransfer->setExceptionMessage($e->getMessage());
            $this->getLogger()->error($e->getMessage());

        }
        return $searchHubRequestTransfer;
    }

    /**
     * Get Http Client
     *
     * @throws Exception
     *
     * @return ClientInterface
     */
    protected function getHttpClient(): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float) Config::get(SearchHubConstants::REQUEST_TIMEOUT, 0.01),
            ]);
        }
        return $this->httpClient;
    }

    /**
     * Get Request Uri
     *
     * @throws Exception
     *
     * @param string $userQuery
     *
     * @return string
     */
    protected function getRequestUri(string $userQuery): string
    {
        $endpoint = Config::get(SearchHubConstants::ENDPOINT);
        return $endpoint . '?' . http_build_query(
                ['userQuery' => $userQuery],
                '',
                '&'
            );
    }
}
