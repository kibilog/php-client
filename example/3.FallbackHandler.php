<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Kibilog\SimpleClient\Fallback\Adapter\FilesystemAdapter;
use Kibilog\SimpleClient\Fallback\Adapter\IAdapter;
use Kibilog\SimpleClient\Fallback\Consumer;
use Kibilog\SimpleClient\Fallback\FallbackMessage;
use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\Monolog;

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
 * If the fallback adapter is set, it will be called automatically for all errors (more
 *   precisely, always when it is impossible to connect to the kibilog server, as well as
 *   for all 4xx and 5xx statuses).
 * We have made a FilesystemAdapter that allows you to save messages in a file structure.
 */
$oClient->setFallback(
    new FilesystemAdapter(
        dirname($_SERVER['DOCUMENT_ROOT']).'/kibilogFallback'
    )
);

/**
 * If FilesystemAdapter does not suit you (for example, you prefer to store messages in Redis),
 *   you can make your own adapter.
 */
class MyAdapter implements IAdapter
{
    public function save(FallbackMessage $fallbackMessage): void
    {
        // TODO: Implement save() method.
    }

    public function consume(HttpClient $httpClient): void
    {
        // TODO: Implement consume() method.
    }
}

$oClient->setFallback(new MyAdapter());


/**
 * ============
 *   CONSUMER
 * ============
 *
 * Let's try to resend the messages.
 * For your convenience, we have made Consumer. We expect you to call it using cron.
 * According to his idea, he receives all unsent messages, after which he tries to
 *   send them again one after another.
 */

$oClient = new HttpClient('01fdenedhya3fwa1nwy9b2pr94');
$oAdapter = new FilesystemAdapter(dirname($_SERVER['DOCUMENT_ROOT']).'/kibilogFallback');
$oClient->setFallback($oAdapter);

(new Consumer($oClient, $oAdapter))->execute();