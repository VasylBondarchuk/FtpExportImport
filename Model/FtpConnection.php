<?php

declare(strict_types = 1);

namespace Training\FtpOrderExport\Model;

use Magento\Framework\Filesystem\Io\Ftp;

class FtpConnection
{
    /**
     * 
     * @var Ftp
     */
    private Ftp $ftp;
    /**
     * 
     * @var string
     */
    private string $ftpConnFailureReason;
    /**
     * 
     * @var type
     */
    private FtpConnFailureEmail $ftpConnFailureEmail;
    /**
     * 
     * @var type
     */
    private FtpDetails $ftpDetails;
    /**
     * 
     * @var Configs
     */
    private Configs $configs;

    public function __construct(
        Ftp $ftp,
        FtpConnFailureEmail $ftpConnFailureEmail,        
        Configs $configs    
    ) {
        $this->ftp = $ftp;
        $this->ftpConnFailureEmail = $ftpConnFailureEmail;        
        $this->configs = $configs;
    }

    /**
     * 
     * @return bool
     */
    public function ftpConnection()
    {        
        try {
            $connection = $this->ftp->open($this->getFtpDetails());
        } catch (\Exception $e) {
            $this->ftpConnFailureReason = $e->getMessage();
            $connection = false;
        }
        return $connection;
    }

    /**
     * 
     * @return bool
     */
    public function isConnSuccessful(): bool
    {
        $numberOfAttempts = $this->getNumberOfAttempts();

        for ($i = 0; $i < $numberOfAttempts; $i++) {
            if ($this->ftpConnection()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @return string
     */
    public function getConnFailureReason() : string
    {
        return $this->ftpConnFailureReason;        
    }

    /**
     * 
     */
    public function sendFtpConnFailureEmail()
    {
        $this->ftpConnFailureEmail->sendFtpConnFailureEmail($this->getConnFailureReason());
    }
    
    /**
     * 
     * @return type
     */
    private function getFtpDetails()
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
    
    /**
     * 
     * @return type
     */
    private function getNumberOfAttempts()
    {
        return ($this->configs->getConnAttempts() > 0)
            ? $this->configs->getConnAttempts() : Configs::DEFAULT_FTP_CONN_ATTEMPTS;
    }
}
