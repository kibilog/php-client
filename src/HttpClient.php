<?php

namespace Kibilog\SimpleClient;

use Exception;
use Kibilog\SimpleClient\Exception\BadResponseException;
use Kibilog\SimpleClient\Exception\KibilogException;
use Kibilog\SimpleClient\Exception\StreamException;
use Kibilog\SimpleClient\Exception\TimeoutException;
use Kibilog\SimpleClient\Fallback\Adapter\IAdapter;
use Kibilog\SimpleClient\Fallback\FallbackMessage;
use Kibilog\SimpleClient\Message\IMessage;
use Kibilog\SimpleClient\Message\Monolog;
use Kibilog\SimpleClient\Response\Response;
use Kibilog\SimpleClient\Response\ResponseCollection;

use function json_encode;

class HttpClient
{

    /** @var string $sUserToken */
    private $sUserToken;
    /** @var int $iHttpTimeout */
    private $iHttpTimeout = 2;
    /** @var IAdapter $fallbackAdapter */
    private $fallbackAdapter;
    /** @var array $aMessages */
    private $aMessages = [];

    /** @var string $sUrl */
    private $sUrl = 'https://kibilog.com/api/v1/log/';

    /**
     * @param string $sUserToken
     *
     * @throws Exception
     */
    public function __construct(string $sUserToken)
    {
        $this->sUserToken = $sUserToken;

        if ((int)ini_get('allow_url_fopen') < 1) {
            throw new Exception('Parameter "allow_url_fopen" in php.ini must be equal to "1".');
        }
    }

    /**
     * @param int $iHttpTimeout
     */
    public function setHttpTimeout(int $iHttpTimeout): void
    {
        $this->iHttpTimeout = $iHttpTimeout;
    }

    /**
     * @param IAdapter $adapter
     */
    public function setFallback(IAdapter $adapter): void
    {
        $this->fallbackAdapter = $adapter;
    }

    /**
     * @param IMessage $message
     *
     * @return Response
     */
    public function sendImmediately(IMessage $message): Response
    {
        $sUrl = null;
        switch ($message::getMessageType()) {
            case Monolog::getMessageType():
                $sUrl = $this->sUrl.'monolog/'.$message->getLogUuid();
                break;
        }

        $oResponse = $this->sendRequest(
            $sUrl,
            [$message->extractData()]
        );

        if (
            !$oResponse->isSuccess()
            && $this->fallbackAdapter instanceof IAdapter
        ) {
            $oFallback = new FallbackMessage();
            $oFallback->setMessages([$message]);
            $oFallback->setResponse($oResponse);

            ($this->fallbackAdapter)->save($oFallback);
        }
        return $oResponse;
    }


    /**
     * @param          $stream
     * @param Response $oResponse
     *
     * @throws BadResponseException
     * @throws StreamException
     * @throws TimeoutException
     */
    private function processResponse($stream, Response $oResponse): void
    {
        if (!$stream) {
            throw new StreamException('Can\'t open stream.');
        }

        $aResponseMeta = stream_get_meta_data($stream);

        if ($aResponseMeta['timed_out'] === true) {
            throw new TimeoutException('The response waiting time has been exceeded. The current timeout value is '.$this->iHttpTimeout.' seconds.');
        }

        $oResponse->setBody(json_decode(stream_get_contents($stream), true));

        foreach ($aResponseMeta['wrapper_data'] as $headerline) {
            if (preg_match('/^HTTP\/(\d+\.\d+)\s+(\d+)\s*(.+)?$/', $headerline, $result) === 1) {
                $oResponse->setStatusCode((int)$result[2]);
                break;
            }
        }

        if (
            $oResponse->getStatusCode() >= 400
            && $oResponse->getStatusCode() < 600
        ) {
            throw new BadResponseException('Response code is '.$oResponse->getStatusCode().'. Response body "'.$oResponse->getBody()['error'].'".');
        }
    }

    /**
     * @param IMessage $message
     */
    public function addMessage(IMessage $message): void
    {
        $this->aMessages[$message::getMessageType()][$message->getLogUuid()][] = $message;
    }

    /**
     *
     */
    public function destroyMessages(): void
    {
        $this->aMessages = [];
    }

    /**
     * @return ResponseCollection
     */
    public function sendMessages(): ResponseCollection
    {
        $oBatchResponse = new ResponseCollection();

        if (!empty($this->aMessages)) {
            foreach ($this->aMessages as $sMessageType => $aLogs) {
                foreach ($aLogs as $sLogUuid => $aMessages) {
                    $sUrl = null;
                    switch ($sMessageType) {
                        case Monolog::getMessageType():
                            $sUrl = $this->sUrl.'monolog/'.$sLogUuid;
                            break;
                    }

                    array_walk($aMessages, function (IMessage &$message)
                        {
                            $message = $message->extractData();
                        });

                    $oResponse = $this->sendRequest(
                        $sUrl,
                        $aMessages
                    );

                    $oBatchResponse->addResponses($oResponse);

                    if (!$oResponse->isSuccess()) {
                        $oBatchResponse->setIsSuccess(false);

                        if ($this->fallbackAdapter instanceof IAdapter) {
                            $oFallback = new FallbackMessage();
                            $oFallback->setMessages($this->aMessages[$sMessageType][$sLogUuid]);
                            $oFallback->setResponse($oResponse);

                            ($this->fallbackAdapter)->save($oFallback);
                        }
                    }
                }
            }
            $this->destroyMessages();
        }

        return $oBatchResponse;
    }

    /**
     * @param $sUrl
     * @param $aData
     *
     * @return Response
     */
    private function sendRequest($sUrl, $aContent)
    {
        $oResponse = new Response();

        try {
            $context = stream_context_create(
                [
                    'http' => [
                        'method' => 'POST',
                        'header' => [
                            'Content-Type: application/json',
                            'apiToken: '.$this->sUserToken
                        ],
                        'timeout' => $this->iHttpTimeout,
                        'content' => json_encode($aContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'ignore_errors' => true
                    ]
                ]
            );

            $stream = fopen(
                $sUrl,
                'r',
                false,
                $context
            );

            $this->processResponse($stream, $oResponse);
            $oResponse->setIsSuccess(true);

            return $oResponse;
        } catch (KibilogException $e) {
            $oResponse->setIsSuccess(false);
            $oResponse->setError($e->getMessage());
        }

        return $oResponse;
    }
}