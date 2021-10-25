<?php

namespace Kibilog\SimpleClient\Fallback\Adapter;

use Exception;
use Kibilog\SimpleClient\Fallback\FallbackMessage;
use Kibilog\SimpleClient\HttpClient;
use Kibilog\SimpleClient\Message\IMessage;
use Throwable;

class FilesystemAdapter implements IAdapter
{
    private $sDirPath;

    /**
     * @param string $sDirPath
     *
     * @throws Exception
     */
    public function __construct(string $sDirPath)
    {
        $sDirPath = trim($sDirPath);
        if (substr($sDirPath, -1) === '/') {
            $sDirPath = substr($sDirPath, 0, -1);
        }


        if (!file_exists($sDirPath)) {
            if (!mkdir($sDirPath, 0777, true)) {
                throw new Exception('Unable to create a directory "'.$sDirPath.'".');
            }
        }

        $this->sDirPath = $sDirPath;
    }


    /**
     * @param FallbackMessage $fallbackMessage
     *
     * @throws Exception
     */
    public function save(FallbackMessage $fallbackMessage): void
    {
        $sSer = serialize($fallbackMessage->getMessages());
        file_put_contents($this->sDirPath.'/'.time().'_'.bin2hex(random_bytes(10)).'.txt', $sSer);
    }

    /**
     * @param HttpClient $httpClient
     */
    public function consume(HttpClient $httpClient): void
    {
        if ($oHandle = opendir($this->sDirPath)) {
            while (false !== ($entry = readdir($oHandle))) {
                if (
                    $entry === "."
                    || $entry === ".."
                    || is_dir($entry)
                    || substr($entry, 0, 1) === '_'
                ) {
                    continue;
                }

                try {
                    rename(
                        $this->sDirPath.'/'.$entry,
                        $this->sDirPath.'/_'.$entry
                    );

                    $aMessages = file_get_contents($this->sDirPath.'/_'.$entry);
                    if (!!$aMessages) {
                        $aMessages = unserialize($aMessages);
                        if (!empty($aMessages)) {
                            foreach ($aMessages as $oMessage) {
                                /** @var IMessage $oMessage */
                                $httpClient->addMessage($oMessage);
                            }
                            $httpClient->sendMessages();
                        }
                    }
                } catch (Throwable $e) {
                }
                finally {
                    if (file_exists($this->sDirPath.'/_'.$entry)) {
                        unlink($this->sDirPath.'/_'.$entry);
                    }
                }
            }
            closedir($oHandle);
        }
    }
}