<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Display;

class FtpDetails
{
    private $configs;

    public function __construct(Configs $configs)
    {
        $this->configs = $configs;
    }

    public function getFtpDetails()
    {
        $connDetails =
            [
                'host' => $this->configs->getFtpHost(),
                'user' => $this->configs->getFtpUserName(),
                'password' => $this->configs->getFtpUserPass(),
                'ssl' => false,
                'passive' => false
            ];

        return $connDetails;
    }

    public function getNumberOfAttempts()
    {
        return ($this->configs->getConnAttempts() > 0)
            ? $this->configs->getConnAttempts() : Configs::DEFAULT_FTP_CONN_ATTEMPTS;
    }
}
