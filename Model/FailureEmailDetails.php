<?php

declare(strict_types = 1);

namespace Training\FtpOrderExport\Model;

use Training\FtpOrderExport\Helper\Email;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class FailureEmailDetails
{
    protected $email;
    protected $storeManager;
    protected $urlInterface;

    public function __construct
    (
        Email $email,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface
    )
    {
        $this->email = $email;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
    }

    public function getEmailDetails(array $emailDetailsNames, array $emailDetailsValues) : array
    {
        return array_combine($emailDetailsNames, $emailDetailsValues);
    }

    public function getSenderDetails(array $senderDetailsValues) : array
    {
        return $this->getEmailDetails(['name','email'],$senderDetailsValues);
    }

    public function getTemplateVars(array $templateVarValues) : array
    {
        return $this->getEmailDetails(['recipientName', 'link', 'senderName', 'reason'], $templateVarValues);
    }

    public function getTemplateOptions() : array
    {
        return $this->getEmailDetails(
            ['area', 'store'],
            [\Magento\Framework\App\Area::AREA_ADMINHTML, $this->storeManager->getStore()->getId()]);
    }

    public function getRecipientEmail($recipientEmail) : string
    {
        return $recipientEmail;
    }

    public function getTemplateIdentifier($templateIdentifier) : string
    {
        return $templateIdentifier;
    }

    public function getLink($pathFromBaseUrl = '') : string
    {
        return $this->urlInterface->getBaseUrl() . $pathFromBaseUrl;
    }

    public function sendFailureEmail($senderDetails, $recipientEmail, $templateIdentifier, $templateOptions, $templateVars)
    {
        $this->email->sendEmail($senderDetails, $recipientEmail, $templateIdentifier, $templateOptions, $templateVars);
    }
}