<?php

namespace Kibilog\SimpleClient;

use Closure;
use Exception;
use Kibilog\SimpleClient\Exception\BadResponseException;
use Kibilog\SimpleClient\Exception\KibilogException;
use Kibilog\SimpleClient\Exception\StreamException;
use Kibilog\SimpleClient\Exception\TimeoutException;
use Kibilog\SimpleClient\Message\IMessage;

class HttpClient
{

    /** @var string $sUserToken */
    private $sUserToken;
    /** @var int $iHttpTimeout */
    private $iHttpTimeout = 2;
    /** @var Closure $fallbackFunc */
    private $fallbackFunc;

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
     * @param Closure $closure
     */
    public function setFallback(Closure $closure): void
    {
        $this->fallbackFunc = $closure;
    }

    /**
     * @param IMessage $message
     *
     * @return Response
     */
    public function sendImmediately(IMessage $message): Response
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
                        'content' => json_encode($message->extractData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'ignore_errors' => true
                    ]
                ]
            );

            $stream = fopen(
                $this->sUrl.'monolog/single/'.$message->getLogUuid(),
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
        finally {
            if (
                !$oResponse->isSuccess()
                && $this->fallbackFunc instanceof Closure
            ) {
                $oFallback = new Fallback();
                $oFallback->setMessage($message);
                $oFallback->setResponse($oResponse);


                ($this->fallbackFunc)($oFallback);
            }
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
            throw new BadResponseException('Response code is '.$oResponse->getStatusCode());
        }
    }
}