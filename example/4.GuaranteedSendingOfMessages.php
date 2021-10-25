<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Kibilog\SimpleClient\Fallback\Adapter\FilesystemAdapter;
use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\Monolog;

$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

/**
 * It is extremely disappointing to lose data in case of an unexpected
 *   termination of the script.
 * To avoid this, you should use "register_shutdown_function".
 * You can be sure that the data will not be lost.
 * We recommend using this together with our fallback handler.
 */

$oClient->setFallback(
    new FilesystemAdapter(
        dirname($_SERVER['DOCUMENT_ROOT']).'/kibilogFallback'
    )
);

register_shutdown_function(function () use ($oClient)
    {
        $oClient->sendMessages();
    });

// ...

$oMessage = (new Monolog(
    '01fjqbwk1heyv50z99hkg7m6ky',
    'Message 1'
))
    ->setLevel(Monolog::LEVEL_DEBUG);

$oClient->addMessage($oMessage);

$oMessage = (new Monolog(
    '01fjqbwk1heyv50z99hkg7m6ky',
    'Message 2'
))
    ->setLevel(Monolog::LEVEL_DEBUG);

$oClient->addMessage($oMessage);

/**
 * Calling Fatal Error.
 * However, the messages will still be sent.
 * And if there are problems with the network, the "fallback"
 *   method will intercept the work for itself.
 */
throw new Error('You shall not pass!');

/**
 * PHP Fatal error:  Uncaught Error: You shall not pass! in /var/www/kibilog.local/test.php:49
 * Stack trace:
 * #0 {main}
 * thrown in /var/www/kibilog.local/test.php on line 49
 */