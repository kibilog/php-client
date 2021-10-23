<?php

namespace Kibilog\SimpleClient\Message;

interface IMessage
{
    public function getLogUuid(): string;

    public function extractData(): array;
}