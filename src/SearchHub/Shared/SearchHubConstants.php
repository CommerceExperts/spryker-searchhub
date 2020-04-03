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

    /**
     * @var string
     */
    public const MAPPING_QUERIES_ENDPOINT = 'SEARCHHUB:MAPPING_QUERIES_ENDPOINT';

    /**
     * @var string
     */
    public const MAPPING_SUGGESTS_ENDPOINT = 'SEARCHHUB:MAPPING_SUGGESTS_ENDPOINT';

    /**
     * @var string
     */
    public const MAPPING_LASTMODIFIED_ENDPOINT = 'SEARCHHUB:MAPPING_LASTMODIFIED_ENDPOINT';

    /**
     *
     */
    public const MAPPING_CACHE = 'SEARCHHUB:MAPPING_CACHE';

    /**
     * TTL in seconds
     */
    public const MAPPING_CACHE_TTL = 'SEARCHHUB:MAPPING_CACHE_TTL';

    /**
     * @var string
     */
    public const API_KEY = 'SEARCHHUB:API_KEY';

    /**
     * @var string
     */
    public const ACCOUNT_NAME = 'SEARCHHUB:ACCOUNT_NAME';

    /**
     *
     */
    public const USE_SAAS_MODE = 'SEARCHHUB:USE_SAAS_MODE';

}
