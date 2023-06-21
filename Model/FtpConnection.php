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
    private FailureEmailDetails $failureEmailDetails;    
    /**
     * 
     * @var Configs
     */
    private Configs $configs;

    public function __construct(
        Ftp $ftp,
        FailureEmailDetails $failureEmailDetails,        
        Configs $configs    
    ) {
        $this->ftp = $ftp;
        $this->failureEmailDetails = $failureEmailDetails;        
        $this->configs = $configs;
    }

    /**
     * 
     * @return bool
     */
    public function getFtpConnection()
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
            if ($this->getFtpConnection()) {
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
    
    public function sendFtpConnFailureEmail()
    {
        $this->failureEmailDetails->sendFailureEmail
        (
            $this->failureEmailDetails->getSenderDetails(["TSG","office@transoftgroup.com"]),
            $this->failureEmailDetails->getRecipientEmail('email@email.com'),
            $this->failureEmailDetails->getTemplateIdentifier('email_ftp_failure_template'),
            $this->failureEmailDetails->getTemplateOptions(),
            $this->failureEmailDetails->getTemplateVars(['Customer', $this->failureEmailDetails->getLink(Configs::FTP_CONFIGS_PATH), "TSG", $this->getConnFailureReason()])
        );
    }
}
