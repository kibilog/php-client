<?php

namespace Kibilog\SimpleClient\Message;

class Monolog implements IMessage
{
    public const LEVEL_DEBUG = 'DEBUG';
    public const LEVEL_INFO = 'INFO';
    public const LEVEL_NOTICE = 'NOTICE';
    public const LEVEL_WARNING = 'WARNING';
    public const LEVEL_ERROR = 'ERROR';
    public const LEVEL_CRITICAL = 'CRITICAL';
    public const LEVEL_ALERT = 'ALERT';
    public const LEVEL_EMERGENCY = 'EMERGENCY';

    /** @var string $__logUuid */
    private $__logUuid;

    /** @var int $createdAt */
    private $createdAt;

    /** @var string $message */
    private $message;

    /** @var string $level */
    private $level = 'INFO';

    /** @var array $params */
    private $params;

    /** @var string $group */
    private $group;

    /**
     * @param string $sLogUuid
     */
    public function __construct(string $sLogUuid)
    {
        $this->__logUuid = $sLogUuid;
        $this->createdAt = time();
    }

    /**
     * @return string
     */
    public function getLogUuid(): string
    {
        return $this->__logUuid;
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
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return Monolog
     */
    public function setMessage(string $message): Monolog
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param string $level
     *
     * @return Monolog
     */
    public function setLevel(string $level): Monolog
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
}