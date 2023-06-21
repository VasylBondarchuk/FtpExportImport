<?php
declare(strict_types = 1);

namespace Training\FtpOrderExport\Model;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;

class OrderedProductTypes implements OptionSourceInterface
{   

    /**
     * @var ConfigInterface
     */
    protected $config;
    
    /**
     * 
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;              
    }

    /**
     * Get product types
     *
     * @return array
     */
    public function getProductTypes() : array
    {        
        $productTypes = $this->config->getAll();
            foreach ($productTypes  as $productTypeKey => $productTypeConfig) {
                $productTypes[$productTypeKey]['label'] = __($productTypeConfig['label']);
            }
        return $productTypes;
    }      

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getProductTypes() as $productTypeId => $productType) {
            $options[] = ['value' => $productTypeId, 'label' => $productType['label']];            
        }
        return $options;
    }

    /**
     * 
     * @return string
     */
    public function getAllProductTypes() : string
    {
        $types = '';
        foreach ($this->toOptionArray() as $items) {
            foreach ($items as $key => $value) {
                if ($key == 'value') {
                    $types .= $value . ',';
                }
            }
        }
        return trim($types, ',');
    }
}
