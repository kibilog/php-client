<?php

namespace Kibilog\SimpleClient;

use Kibilog\SimpleClient\Message\IMessage;

class Fallback
{
    /** @var Response $response */
    private $response;
    /** @var IMessage */
    private $message;

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
     * @return IMessage
     */
    public function getMessage(): IMessage
    {
        return $this->message;
    }

    /**
     * @param IMessage $message
     */
    public function setMessage(IMessage $message): void
    {
        $this->message = $message;
    }
}