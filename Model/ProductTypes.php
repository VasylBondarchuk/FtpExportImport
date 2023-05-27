<?php

namespace Training\FtpExportImport\Model;

use Magento\Catalog\Model\Product\Type\Pool;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ProductTypes implements OptionSourceInterface
{   

    /**
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * Product types
     *
     * @var array|string
     */
    protected $_types;

    /**
     * Composite product type Ids
     *
     * @var array
     */
    protected $_compositeTypes;

    /**
     * Price models
     *
     * @var array
     */
    protected $_priceModels;

    /**
     * Product types by type indexing priority
     *
     * @var array
     */
    protected $_typesPriority;

    /**
     * Product type factory
     *
     * @var Pool
     */
    protected $_productTypePool;
    
    /**
     * Construct
     *
     * @param ConfigInterface $config
     * @param Pool $productTypePool
     * @param PriceFactory $priceFactory
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        ConfigInterface $config,
        Pool $productTypePool        
    ) {
        $this->_config = $config;
        $this->_productTypePool = $productTypePool;        
    }    

    /**
     * Get product type labels array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getTypes() as $typeId => $type) {
            $options[$typeId] = (string)$type['label'];
        }
        return $options;
    }

    /**
     * Get product type labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Get product type labels array with empty value for option element
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get product type labels array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get product type label
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return $options[$optionId] ?? null;
    }

    /**
     * Get product types
     *
     * @return array
     */
    public function getTypes()
    {
        if ($this->_types === null) {
            $productTypes = $this->_config->getAll();
            foreach ($productTypes as $productTypeKey => $productTypeConfig) {
                $productTypes[$productTypeKey]['label'] = __($productTypeConfig['label']);
            }
            $this->_types = $productTypes;
        }
        return $this->_types;
    }      

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    public function getAllProductTypes()
    {
        $types= '';
        foreach ($this->toOptionArray() as $items) {
            foreach ($items as $key => $value) {
                if ($key == 'value') {
                    $types .= $value .',';
                }
            }
        }
        return trim($types, ',');
    }
}
