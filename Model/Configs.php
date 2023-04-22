<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Training\FtpExportImport\Model\OrderStatus;
use Training\FtpExportImport\Model\ProductTypes;

class Configs
{
    const FTP_HOST = 'improvements/general/ftp_host';
    const FTP_USER_NAME = 'improvements/general/user_name';
    const FTP_USER_PASS = 'improvements/general/user_password';
    const FTP_CONN_ATTEMPTS = 'improvements/general/connection_attempts';
    const DEFAULT_FTP_CONN_ATTEMPTS = 5;
    const ORDER_STATUS = 'improvements/general/order_status';
    const PRODUCTS_TYPES = 'improvements/general/products_types';
    const MULTISELECT_VALUES = [self::ORDER_STATUS,self::PRODUCTS_TYPES];

    private $scopeConfig;
    private $orderStatuses;
    private $productTypes;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OrderStatus $orderStatuses,
        ProductTypes $productTypes
    ) {
        $this->scopeConfig= $scopeConfig;
        $this->orderStatuses = $orderStatuses;
        $this->productTypes = $productTypes;
    }

    public function getConfigs(string $configPath): ?string
    {
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    public function getFtpHost(): string
    {
        return $this->getConfigs(self::FTP_HOST);
    }

    public function getFtpUserName(): string
    {
        return $this->getConfigs(self::FTP_USER_NAME);
    }

    public function getFtpUserPass(): string
    {
        return $this->getConfigs(self::FTP_USER_PASS);
    }

    public function getConnAttempts(): string
    {
        return $this->getConfigs(self::FTP_CONN_ATTEMPTS);
    }

    public function getSelectedOrderStatus()
    {
        return $this->getConfigs(self::ORDER_STATUS)
               ? $this->getConfigs(self::ORDER_STATUS)
               : $this->orderStatuses->getAllStatuses();
    }

    public function getSelectedProductsTypes()
    {
        return $this->getConfigs(self::PRODUCTS_TYPES)
             ? $this->getConfigs(self::PRODUCTS_TYPES)
             : $this->productTypes->getAllProductTypes();
    }

    public function getMultiselectValues()
    {
        $multiselectValues = [];
        foreach (self::MULTISELECT_VALUES as $multiselectValue) {
            $multiselectValues[] = $this->scopeConfig->getValue($multiselectValue, ScopeInterface::SCOPE_STORE);
        }
        return $multiselectValues;
    }
}
