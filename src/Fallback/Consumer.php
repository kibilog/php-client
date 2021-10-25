<?php

namespace Kibilog\SimpleClient\Fallback;

use Kibilog\SimpleClient\Fallback\Adapter\IAdapter;
use Kibilog\SimpleClient\HttpClient;

class Consumer
{
    /**
     * @var HttpClient
     */
    private $httpClient;
    /**
     * @var IAdapter
     */
    private $adapter;

    /**
     * @param HttpClient $httpClient
     * @param IAdapter   $adapter
     */
    public function __construct(HttpClient $httpClient, IAdapter $adapter)
    {
        $this->httpClient = $httpClient;
        $this->adapter = $adapter;
    }

    /**
     *
     */
    public function execute(): void
    {
        $this->adapter->consume($this->httpClient);
    }
}