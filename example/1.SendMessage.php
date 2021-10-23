<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\Monolog;

$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

$oMessage = (new Monolog('01fjqbwk1heyv50z99hkg7m6ky'))
    ->setCreatedAt(time())
    ->setMessage('The application got started.')
    ->setLevel(Monolog::LEVEL_INFO);

/**
 * Calling the "send Immediately" method will instantly send a message to kibilog.
 */
$oResponse = $oClient->sendImmediately($oMessage);

/**
 * // Example success response
 *
 * Kibilog\SimpleClient\Response {
 *   -iStatusCode: 200
 *   -aBody: array:1 [
 *     "done" => true
 *   ]
 *   -isSuccess: true
 *   -sError: null
 * }
 *
 *
 *
 * // Examples error response
 *
 * Kibilog\SimpleClient\Response {
 *   -iStatusCode: 520
 *   -aBody: null
 *   -isSuccess: false
 *   -sError: "Can't open stream."
 * }
 *
 * Kibilog\SimpleClient\Response {
 *   -iStatusCode: 404
 *   -aBody: array:1 [
 *     "error" => "Not Found"
 *   ]
 *   -isSuccess: false
 *   -sError: "Response code is 404"
 * }
 */
if ($oResponse->isSuccess()) {
    print_r($oResponse->getBody());
} else {
    print_r($oResponse->getError());
}