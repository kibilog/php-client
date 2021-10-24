<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\Monolog;
use Kibilog\SimpleClient\Response\Response;

$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

/**
 * While the application is running, you need to collect messages.
 * All registered messages are collected in a collection and sent
 *   only when the corresponding method is called.
 */
$oMessage = (new Monolog(
    '01fjqbwk1heyv50z99hkg7m6ky',
    'Failed to connect to the server to receive weather data.'
))
    ->setLevel(Monolog::LEVEL_WARNING);

$oClient->addMessage($oMessage);

// ...

/**
 * You can register messages for several loggers at once.
 * The ulead of the logger is set via the first argument of the message.
 */
$oMessage = (new Monolog(
    '01fe3nmc2fet0pea9t38pec3gg',
    'Failed to connect to the database.'
))
    ->setLevel(Monolog::LEVEL_EMERGENCY);

$oClient->addMessage($oMessage);


/**
 * When you see fit, send all the messages at once.
 * Note that sending messages resets the previously collected collection.
 */
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
 * Calling the "sendImmediately" method will instantly send a message
 *   to the Kibilog.com.
 * This will not affect previously registered messages, because it
 *   is sent bypassing the collection mechanism.
 */
$oResponse = $oClient->sendImmediately($oMessage);

if (!$oResponse->isSuccess()) {
    echo $oResponse->getError();
}