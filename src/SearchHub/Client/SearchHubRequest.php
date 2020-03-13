<?php

namespace SearchHub\Client;

class SearchHubRequest
{

    /**
     * @var string|null
     */
    protected $userQuery;

    /**
     * @var string|null
     */
    protected $searchQuery;

    /**
     * @var boolean|null
     */
    protected $isException;

    /**
     * @var string|null
     */
    protected $exceptionMessage;


    public function __construct(string $userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /**
     * @param string|null $userQuery
     *
     * @return $this
     */
    public function setUserQuery($userQuery)
    {
        $this->userQuery = $userQuery;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserQuery()
    {
        return $this->userQuery;
    }

    /**
     * @param string|null $searchQuery
     *
     * @return $this
     */
    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param boolean|null $isException
     *
     * @return $this
     */
    public function setIsException($isException)
    {
        $this->isException = $isException;

        return $this;
    }

    /**
     * @return boolean|null
     */
    public function getIsException()
    {
        return $this->isException;
    }

    /**
     * @param string|null $exceptionMessage
     *
     * @return $this
     */
    public function setExceptionMessage($exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExceptionMessage()
    {
        return $this->exceptionMessage;
    }

}
