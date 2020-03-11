<?php

declare(strict_types=1);

namespace SearchHub\Shared;

/**
 * Interface SearchHubConstants
 * @package SearchHub\Shared
 */
interface SearchHubConstants
{
    /**
     * @var string
     */
    public const SMARTSUGGEST_ENDPOINT = 'SEARCHHUB:SMARTSUGGEST_ENDPOINT';

    /**
     * @var string
     */
    public const SMARTQUERY_ENDPOINT = 'SEARCHHUB:SMARTQUERY_ENDPOINT';

    /**
     * @var string
     */
    public const REQUEST_TIMEOUT = 'SEARCHHUB:REQUEST_TIMEOUT';
}
