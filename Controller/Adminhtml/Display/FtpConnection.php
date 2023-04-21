<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Display;

use Magento\Framework\Filesystem\Io\Ftp;

class FtpConnection
{
    private $ftp;
    private $ftpConnFailureReason;
    private $ftpConnFailureEmail;
    private $ftpDetails;

    public function __construct(
        Ftp $ftp,
        FtpConnFailureEmail $ftpConnFailureEmail,
        FtpDetails $ftpDetails
    ) {
        $this->ftp = $ftp;
        $this->ftpConnFailureEmail = $ftpConnFailureEmail;
        $this->ftpDetails = $ftpDetails;
    }

    public function ftpConnection()
    {
        $connection = false;
        try {
            $connection = $this->ftp->open($this->ftpDetails->getFtpDetails());
        } catch (\Exception $e) {
            $this->ftpConnFailureReason = $e->getMessage();
        }
        return $connection;
    }

    public function isConnSuccessful() : bool
    {
        $numberOfAttempts = $this->ftpDetails->getNumberOfAttempts();

        for ($i = 0; $i < $numberOfAttempts; $i++) {
            if ($this->ftpConnection()) {
                return true;
            }
        }
        return false;
    }

    public function getConnFailureReason()
    {
        return $this->ftpConnFailureReason;
    }

    public function sendFtpConnFailureEmail()
    {
        $this->ftpConnFailureEmail->sendFtpConnFailureEmail($this->getConnFailureReason());
    }
}
