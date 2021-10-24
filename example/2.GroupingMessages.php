<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Kibilog\SimpleClient\Assistent\Uuid;
use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\Monolog;

$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

/**
 * If you need to collect logs within the same logic in chronological
 *   order, the best thing you can do is group them.
 * This can be useful if the script is executed in multiple threads.
 * Consider below the logging of the code that should download the
 *   image and process it.
 * In order to group messages, it is necessary to pass the same
 *   group name to each of them. The group name must be uuid v4.
 */

$sUuidGroup = Uuid::v4();

// ...

$oMessage = (new Monolog(
    '01fjqbwk1heyv50z99hkg7m6ky',
    'Image downloaded.'
))
    ->setLevel(Monolog::LEVEL_INFO)
    ->setGroup($sUuidGroup);

$oClient->addMessage($oMessage);

// ...

$oMessage = (new Monolog(
    '01fjqbwk1heyv50z99hkg7m6ky',
    'The image has been converted to jpeg format.'
))
    ->setLevel(Monolog::LEVEL_INFO)
    ->setGroup($sUuidGroup);

$oClient->addMessage($oMessage);

// ...

$oMessage = (new Monolog(
    '01fjqbwk1heyv50z99hkg7m6ky',
    'Failed to resize the image.'
))
    ->setLevel(Monolog::LEVEL_WARNING)
    ->setGroup($sUuidGroup);

$oClient->addMessage($oMessage);

// ...

$oMessage = (new Monolog(
    '01fjqbwk1heyv50z99hkg7m6ky',
    'An error occurred while saving the image. There is no access to the directory to save the image..'
))
    ->setLevel(Monolog::LEVEL_ALERT)
    ->setGroup($sUuidGroup);

$oClient->addMessage($oMessage);

// ...

$oClient->sendMessages();

