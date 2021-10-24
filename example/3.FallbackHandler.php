<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Kibilog\SimpleClient\Fallback;
use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\Monolog;
use Kibilog\SimpleClient\Response\Response;

$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

$oMessage = (new Monolog('01fjqbwk1heyv50z99hkg7m6ky', 'E-mail was not sent!'))
    ->setLevel(Monolog::LEVEL_ALERT)
    ->setParams(
        [
            'recipient' => 'example@example.com',
            'body' => 'Your account is running out of money.'
        ]
    );
$oClient->addMessage($oMessage);

$oMessage = (new Monolog('01fjqbwk1heyv50z99hkg7m6ky', 'E-mail was not sent!'))
    ->setLevel(Monolog::LEVEL_ALERT)
    ->setParams(
        [
            'recipient' => 'example@example.com',
            'body' => 'Thank you for registering!'
        ]
    );
$oClient->addMessage($oMessage);


/**
 * Fallback is designed not to lose data in case of network problems or service availability.
 * If the fallback function is defined, it will be called automatically for all errors (to be
 *   more precise, always when it is not possible to connect to the kibilog server, as well
 *   as for all statuses 4xx and 5xx).
 * It is assumed that the messages will be collected and re-processed. The simplest example
 *   is to assemble them into files and process them using cron. But it's better to use a
 *   message broker. And it is better not to store unsent messages and logs in a public
 *   directory, as shown in the example.
 */
$oClient->setFallback(function (Fallback $fallback)
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'].'/kibilog_error.log',
            '[ '.$fallback->getResponse()
                ->getStatusCode().' ] '.$fallback->getResponse()
                ->getError().PHP_EOL,
            FILE_APPEND
        );

        /**
         * Saved to a file
         */
        $sSerialize = serialize($fallback->getMessages());
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'].'/kibilog_messages/'.time().'_'.sha1($sSerialize).'.txt',
            $sSerialize
        );
    });

$oResponseCollection = $oClient->sendMessages();
if (!$oResponseCollection->isSuccess()) {
    foreach ($oResponseCollection->getResponses() as $oResponse) {
        /** @var Response $oResponse */
        if (!$oResponse->isSuccess()) {
            echo $oResponse->getError().PHP_EOL;
        }
    }
}


/**
 * /consumer.php
 *
 * Let's try to resend the messages.
 * The time when the message was generated will not be lost.
 */

$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

$aMessages = glob($_SERVER['DOCUMENT_ROOT'].'/kibilog_messages/*');
foreach ($aMessages as &$sFilepath) {
    $oMessages = unserialize(file_get_contents($sFilepath));
    foreach ($oMessages as &$oMessage) {
        $oClient->addMessage($oMessage);
    }
    $oResponse = $oClient->sendMessages();
    if ($oResponse->isSuccess()) {
        unlink($sFilepath);
    }
    unset($oMessage);
}
unset($sFilepath);