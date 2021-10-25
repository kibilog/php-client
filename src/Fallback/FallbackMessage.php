<?php

namespace Kibilog\SimpleClient\Fallback;

use Kibilog\SimpleClient\Response\Response;

class FallbackMessage
{
    /** @var Response $response */
    private $response;
    /** @var iterable */
    private $messages;

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * @return iterable
     */
    public function getMessages(): iterable
    {
        return $this->messages;
    }

    /**
     * @param iterable $messages
     */
    public function setMessages(iterable $messages): void
    {
        $this->messages = $messages;
    }
}