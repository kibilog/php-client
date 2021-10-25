<?php

namespace Kibilog\SimpleClient\Fallback\Adapter;

use Kibilog\SimpleClient\Fallback\FallbackMessage;
use Kibilog\SimpleClient\HttpClient;

interface IAdapter
{
    /**
     * Save messages
     *
     * @param FallbackMessage $fallbackMessage
     */
    public function save(FallbackMessage $fallbackMessage): void;

    /**
     * Consume saved messages
     *
     * @param HttpClient $httpClient
     */
    public function consume(HttpClient $httpClient): void;
}