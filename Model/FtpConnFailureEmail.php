<?php
declare(strict_types = 1);

namespace Training\FtpExportImport\Model;

use Training\FtpExportImport\Model\Configs;

class FtpConnFailureEmail extends FailureEmailDetails
{

    public function sendFtpConnFailureEmail($failureReason)
    {
        $this->sendFailureEmail
        (
            $this->getSenderDetails(["TSG","office@transoftgroup.com"]),
            $this->getRecipientEmail('email@email.com'),
            $this->getTemplateIdentifier('email_ftp_failure_template'),
            $this->getTemplateOptions(),
            $this->getTemplateVars(['Customer', $this->getLink(Configs::FTP_CONFIGS_PATH), "TSG", $failureReason])
        );
    }
}