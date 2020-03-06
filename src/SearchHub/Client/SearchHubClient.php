<?php

declare(strict_types=1);

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use SearchHub\Shared\SearchHubConstants;
use Spryker\Client\Kernel\AbstractClient;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Log\LoggerTrait;

/**
 * Class SearchhubClient
 * @package SearchHub\Client
 */
class SearchHubClient extends AbstractClient implements SearchHubClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    use LoggerTrait;

    /**
     * Optimize Query by sending the userQuery to search|hub
     *
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     * @throws Exception
     *
     */
    public function optimizeQuery(SearchHubRequest $searchHubRequest): SearchHubRequest
    {
        $client = $this->getHttpClient();
        $uri = $this->getRequestUri($searchHubRequest->getUserQuery());
        try {
            $optimizedQuery = $client->get($uri);
            assert($optimizedQuery instanceof Response);
            $searchHubRequest->setSearchQuery($optimizedQuery->getBody()->getContents());
            $searchHubRequest->setIsException(false);
        } catch (Exception $e) {
            $searchHubRequest->setSearchQuery($searchHubRequest->getUserQuery());
            $searchHubRequest->setIsException(true);
            $searchHubRequest->setExceptionMessage($e->getMessage());
            $this->getLogger()->error($e->getMessage());
        }
        return $searchHubRequest;
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
