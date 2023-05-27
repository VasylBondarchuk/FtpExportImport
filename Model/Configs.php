<?php
declare(strict_types = 1);

namespace Training\FtpExportImport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Training\FtpExportImport\Model\OrderStatus;
use Training\FtpExportImport\Model\ProductTypes;

class Configs
{
    const FTP_CONFIGS_PATH = 'admin/system_config/edit/section/export_import_configuration';
    
    // FTP Connection Details
    const EXPORT_ENABLED = 'export_import_configuration/ftp_connection_details/enable';
    const FTP_HOST = 'export_import_configuration/ftp_connection_details/ftp_host';
    const FTP_USER_NAME = 'export_import_configuration/ftp_connection_details/user_name';
    const FTP_USER_PASS = 'export_import_configuration/ftp_connection_details/user_password';
    const FTP_CONN_ATTEMPTS = 'export_import_configuration/ftp_connection_details/connection_attempts';
    const DEFAULT_MAX_CONNECTION_ATEMPTS = 5;
    
    // Exported Orders Details
    const ORDER_STATUS = 'export_import_configuration/exported_orders_details/order_status';
    const PRODUCTS_TYPES = 'export_import_configuration/exported_orders_details/products_types';
    const MULTISELECT_VALUES = [self::ORDER_STATUS, self::PRODUCTS_TYPES];
    

    /**
     * 
     * @var ScopeConfigInterface
     */    
    private ScopeConfigInterface $scopeConfig;
    /**
     * 
     * @var OrderStatus
     */    
    private OrderStatus $orderStatuses;
    /**
     * 
     * @var ProductTypes
     */
    private ProductTypes $productTypes;

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

    public function isExportEnabled(): bool
    {
        return (bool)$this->getConfigs(self::EXPORT_ENABLED);
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
        return $this->getConfigs(self::FTP_CONN_ATTEMPTS)
               ?? self::DEFAULT_MAX_CONNECTION_ATEMPTS;
    }

    public function getSelectedOrderStatus()
    {
        return $this->getConfigs(self::ORDER_STATUS)
               ?? $this->orderStatuses->getAllStatuses();
    }

    public function getSelectedProductsTypes(): string
    {
        return $this->getConfigs(self::PRODUCTS_TYPES)
               ?? $this->productTypes->getAllProductTypes();
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
