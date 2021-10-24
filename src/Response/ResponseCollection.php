<?php

namespace Kibilog\SimpleClient\Response;

class ResponseCollection
{
    /** @var array $responses */
    private $responses = [];

    /** @var bool $isSuccess */
    private $isSuccess = true;

    /**
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * @param Response $response
     *
     * @return ResponseCollection
     */
    public function addResponses(Response $response): ResponseCollection
    {
        $this->responses[] = $response;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     *
     * @return ResponseCollection
     */
    public function setIsSuccess(bool $isSuccess): ResponseCollection
    {
        $this->isSuccess = $isSuccess;
        return $this;
    }


}