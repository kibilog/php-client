<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Kibilog\SimpleClient\Fallback;
use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\Monolog;

$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

$oMessage = (new Monolog('01fjqbwk1heyv50z99hkg7m6ky'))
    ->setMessage('E-mail was not sent!')
    ->setLevel(Monolog::LEVEL_ERROR)
    ->setParams(
        [
            'recipient' => 'example@example.com',
            'body' => 'Your account is running out of money.'
        ]
    );


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
        /**
         * Kibilog\SimpleClient\Fallback {
         *   -response: Kibilog\SimpleClient\Response {
         *     -iStatusCode: 520
         *     -aBody: null
         *     -isSuccess: false
         *     -sError: "Can't open stream."
         *   }
         *   -message: Kibilog\SimpleClient\Message\Monolog {
         *     -__logUuid: "01fjqbwk1heyv50z99hkg7m6ky"
         *     -createdAt: 1635028408
         *     -message: "E-mail was not sent!"
         *     -level: "ERROR"
         *     -params: array:2 [
         *       "recipient" => "example@example.com"
         *       "body" => "Your account is running out of money."
         *     ]
         *     -group: null
         *   }
         * }
         */

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
        $sSerialize = serialize($fallback->getMessage());
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'].'/kibilog_messages/'.time().'_'.sha1($sSerialize).'.txt',
            $sSerialize
        );
    });

$oResponse = $oClient->sendImmediately($oMessage);
if ($oResponse->isSuccess()) {
    print_r($oResponse->getBody());
} else {
    print_r($oResponse->getError());
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
    $oMessage = unserialize(file_get_contents($sFilepath));
    $oResponse = $oClient->sendImmediately($oMessage);
    if ($oResponse->isSuccess()) {
        unlink($sFilepath);
    }
}
unset($sFilepath);