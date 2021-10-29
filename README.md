## The library is a client for working with Kibilog.com

> The client works without cUrl. To work, it is necessary that in php.ini there is a setting allow_url_fopen = 1


[Detailed examples](https://github.com/kibilog/php-client/tree/master/example)

## Example:

```php
use Kibilog\SimpleClient\Fallback\Adapter\FilesystemAdapter;
use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message;

/**
 * Initializing the client.
 */
$sUserToken = '01fdeleozya3fwa1nwy9b2w034';
$oClient = new HttpClient($sUserToken);

/**
 * Set timeout connection.
 * Default 2 seconds.
 * Optional.
 */
$oClient->setHttpTimeout(5);

/**
 * Fallback is designed not to lose data in case
 *   of network problems or service availability.
 * Optional.
 */
$oClient->setFallback(
    new FilesystemAdapter(dirname($_SERVER['DOCUMENT_ROOT']).'/kibilogFallback')
);

/**
 * If the program crashes, we will try to send
 *   all the collected messages.
 * Optional.
 */
register_shutdown_function(function () use ($oClient)
    {
        $oClient->sendMessages();
    }
);

// As an example, let's go through the files and try to convert them.
$sDir = $_SERVER['DOCUMENT_ROOT'].'/images/';
$aFiles = glob($sDir.'*');
foreach ($aFiles as $sFile) {
    $sFilepath = $sDir.$sFile;
    $iTime = microtime();

    $sLogUlid = '01fjqbwk1heyv50z99hkg7m6ky';

    /**
     * If we need to group messages (as part of this example, grouping messages within the
     *   framework of working with a single file), we need to form a message group value.
     * The group value must be UUID version 4.
     */
    $sUuidIteration = \Kibilog\SimpleClient\Assistent\Uuid::v4();

    $oMessage = (new Message\Monolog(
        $sLogUlid,
        'Starting processing the file "'.$sFile.'".'
    ))
        /**
         * By default, time(). It is set automatically during creation.
         * It makes sense to set when transmitting messages with a timestamp (for example, nginx log).
         * There must be a timestamp with UTC timezone.
         * Optional.
         */
        ->setCreatedAt(time())
        /**
         * If necessary, additional parameters can be registered to form an
         *   array with scalar values.
         * Optional.
         */
        ->setParams(
            [
                'createdAt' => date("F d Y H:i:s.", filemtime($sFilepath)),
                'filesize' => round($sFilepath / 1024, 3)
            ]
        )
        /**
         * Log entry level according to RFC 5424 standard.
         * Optional. By default INFO.
         */
        ->setLevel(Message\Monolog::LEVEL_INFO)
        /**
         * A group of messages.
         * Optional.
         */
        ->setGroup($sUuidIteration);
    /**
     * Let's add the message to the collection.
     */
    $oClient->addMessage($oMessage);


    try {
        if (filesize($sFilepath) < 1024) {
            $oClient->addMessage(
                (new Message\Monolog(
                    $sLogUlid,
                    'The image is suspiciously small. Let\'s skip it.'
                ))
                    ->setGroup($sUuidIteration)
                    ->setGroup(Message\Monolog::LEVEL_WARNING)
            );
        }

        // Some kind of logic
        // ...
        
        $oMessage = null;
        if ($isResultSuccess) {
            $oMessage = (new Message\Monolog(
                $sLogUlid,
                'Done'
            ));
        } else {
            $oMessage = (new Message\Monolog(
                $sLogUlid,
                'Error'
            ))->setLevel(Message\Monolog::LEVEL_ERROR);
        }

        $oMessage
            ->setGroup($sUuidIteration)
            ->setParams([
                'executeTime' => round(microtime() - $iTime, 4)
            ]);
        $oClient->addMessage($oMessage);
    } catch (\Throwable $e) {
        $oClient->addMessage(
            (new Message\Monolog(
                $sLogUlid,
                'Exception: '.$e->getMessage()
            ))
                ->setParams(
                    [
                        'trace' => array_map(function ($v)
                            {
                                return (!empty($v['class']) ? $v['class'].'->' : '')
                                       .implode(
                                           ' ',
                                           [
                                               $v['function'].'()',
                                               'IN',
                                               $v['file'],
                                               $v['line']
                                           ]
                                       );
                            }, $e->getTrace())
                    ]
                )
                ->setLevel(Message\Monolog::LEVEL_CRITICAL)
        );
    }
    finally {
        /**
         * We will send the collected messages.
         * After sending, the internal collection will be reset and it
         *   can be reassembled.
         * Important: You can add messages to the collection that are
         *   intended for different logs (with different гдшв). In fact,
         *   it's even better - by sending messages in large groups, you
         *   save time on the connection.
         * But it should be understood that collecting a large number of
         *   messages consumes RAM, so find a balance so as not to get a
         *   leak of RAM.
         */
        $oClient->sendMessages();
    }
}
```