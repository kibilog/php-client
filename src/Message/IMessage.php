<?php

namespace Kibilog\SimpleClient\Message;

interface IMessage
{
    public function getLogUuid(): string;

    public static function getMessageType(): string;

    public function extractData(): array;
}