<?php

declare(strict_types = 1);

namespace Training\FtpOrderExport\Model;

class CsvExportFailureEmail extends FailureEmailDetails
{

    public function sendCsvCreationFailureEmail($reason)
    {
        $this->sendFailureEmail
        (
            $this->getSenderDetails(["TSG","office@transoftgroup.com"]),
            $this->getRecipientEmail('office@transoftgroup.com'),
            $this->getTemplateIdentifier('email_csv_creation_failure_template'),
            $this->getTemplateOptions(),
            $this->getTemplateVars(['Developer', $this->getLink(), "TSG", $reason])
        );
    }
}