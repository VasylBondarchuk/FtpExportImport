<?php
declare(strict_types = 1);

namespace Training\FtpOrderExport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Training\FtpOrderExport\Model\OrderedProductTypes;

class Configs
{
    // Button URL
    const FTP_CONFIGS_PATH = 'adminhtml/system_config/edit/section/export_import_configuration';    
    const EXPORT_ACTION_PATH = 'export/index/export/';
    
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
    const ORDER_ATTRIBUTES = 'export_import_configuration/exported_orders_details/order_statuses';
    const MULTISELECT_VALUES = [self::ORDER_STATUS, self::PRODUCTS_TYPES];    

    /**
     * 
     * @var ScopeConfigInterface
     */    
    private ScopeConfigInterface $scopeConfig;
    
    /**
     * 
     * @var OrderedProductTypes
     */
    private OrderedProductTypes $OrderedProductTypes;

    public function __construct(
        ScopeConfigInterface $scopeConfig,       
        OrderedProductTypes $OrderedProductTypes
    ) {
        $this->scopeConfig= $scopeConfig;        
        $this->OrderedProductTypes = $OrderedProductTypes;
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

    public function getSelectedOrderStatus(): string
    {
        return $this->getConfigs(self::ORDER_STATUS);               
    }

    public function getSelectedProductsTypes(): string
    {
        return $this->getConfigs(self::PRODUCTS_TYPES)
               ?? $this->OrderedProductTypes->getAllProductTypes();
    }
    
    public function getSelectedOrderAttributes(): string
    {
        return $this->getConfigs(self::ORDER_ATTRIBUTES);
               
    }
    
    public function getSelectedOrderAttributesLabels(): array
    {
        $orderAttributes = explode(',', $this->getConfigs(self::ORDER_ATTRIBUTES));
        $orderAttributesLabels = [];
        foreach($orderAttributes as $attribute){
            $orderAttributesLabels[] = $this->formatString($attribute);
        }
        return $orderAttributesLabels;
               
    }
    
    private function formatString(string $inputString): string {
        $replacedString = str_replace('_', ' ', $inputString);
        $formattedString = ucwords($replacedString);
        return $formattedString;
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
