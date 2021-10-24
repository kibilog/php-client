<?php

namespace Kibilog\SimpleClient\Message;

class Monolog implements IMessage
{

    public const LEVEL_DEBUG = 10;
    public const LEVEL_INFO = 20;
    public const LEVEL_NOTICE = 30;
    public const LEVEL_WARNING = 40;
    public const LEVEL_ERROR = 50;
    public const LEVEL_CRITICAL = 60;
    public const LEVEL_ALERT = 70;
    public const LEVEL_EMERGENCY = 80;

    /** @var string $__logUuid */
    private $__logUuid;

    /** @var int $createdAt */
    private $createdAt;

    /** @var string $message */
    private $message;

    /** @var int $level */
    private $level = 20;

    /** @var array $params */
    private $params;

    /** @var string $group */
    private $group;

    /**
     * @param string $sLogUuid
     */
    public function __construct(string $sLogUuid, string $sMessage)
    {
        $this->__logUuid = $sLogUuid;
        $this->message = $sMessage;
        $this->createdAt = time();
    }

    /**
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     *
     * @return Monolog
     */
    public function setCreatedAt(int $createdAt): Monolog
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return Monolog
     */
    public function setLevel(int $level): Monolog
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array $params
     *
     * @return Monolog
     */
    public function setParams(array $params): Monolog
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string $group
     *
     * @return Monolog
     */
    public function setGroup(string $group): Monolog
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogUuid(): string
    {
        return $this->__logUuid;
    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        return [
            'createdAt' => $this->createdAt,
            'message' => $this->message,
            'level' => $this->level,
            'params' => $this->params,
            'group' => $this->group
        ];
    }

    /**
     * @return string
     */
    public static function getMessageType(): string
    {
        return 'monolog';
    }
}