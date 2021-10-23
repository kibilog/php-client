<?php

namespace Kibilog\SimpleClient;

class Response
{
    /** @var int $iStatusCode */
    private $iStatusCode = 520;

    /** @var array $aBody */
    private $aBody = null;

    /** @var bool $isSuccess */
    private $isSuccess = false;

    /** @var string $sError */
    private $sError;

    /**
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->iStatusCode;
    }

    /**
     * @param int $iStatusCode
     *
     * @return Response
     */
    public function setStatusCode(int $iStatusCode): Response
    {
        $this->iStatusCode = $iStatusCode;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getBody(): ?array
    {
        return $this->aBody;
    }

    /**
     * @param array|null $aBody
     *
     * @return $this
     */
    public function setBody(?array $aBody): Response
    {
        $this->aBody = $aBody;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     *
     * @return Response
     */
    public function setIsSuccess(bool $isSuccess): Response
    {
        $this->isSuccess = $isSuccess;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->sError;
    }

    /**
     * @param string $sError
     *
     * @return Response
     */
    public function setError(string $sError): Response
    {
        $this->sError = $sError;
        return $this;
    }
}