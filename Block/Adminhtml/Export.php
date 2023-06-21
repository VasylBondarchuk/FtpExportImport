<?php

declare(strict_types = 1);

namespace Training\FtpOrderExport\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Training\FtpOrderExport\Model\FtpConnection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Training\FtpOrderExport\Model\Configs;
use Training\FtpOrderExport\Model\OrdersDetails;
use Training\FtpOrderExport\Model\OrderedProductTypes;
use Training\FtpOrderExport\Model\OrderAttributes;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;

class Export extends Template
{
    /**
     * 
     * @var FtpConnection
     */
    private FtpConnection $ftpConnection;
    /**
     * 
     * @var CollectionFactory
     */
    private CollectionFactory $orderCollectionFactory;
    /**
     * 
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;
    /**
     * 
     * @var Configs
     */
    private Configs $configs;
    /**
     * 
     * @var OrderStatus
     */
    private OrdersDetails $ordersDetails;
    /**
     * 
     * @var orderedProductTypes
     */
    private orderedProductTypes $orderedProductTypes;
    
    /**
     * 
     * @var OrderAttributes
     */
    private OrderAttributes $orderAttributes;
    /**
     * @var BackendUrlInterface
     */
    private $backendUrlBuilder;

    public function __construct(
        Context $context,
        FtpConnection $ftpConnection,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Configs $configs,
        OrdersDetails $ordersDetails,
        OrderedProductTypes $orderedProductTypes,
        OrderAttributes $orderAttributes,    
        BackendUrlInterface $backendUrlBuilder,    
        array $data = []
    ) {
        $this->ftpConnection = $ftpConnection;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->configs = $configs;
        $this->ordersDetails = $ordersDetails;        
        $this->orderedProductTypes = $orderedProductTypes;
        $this->orderAttributes = $orderAttributes;
        $this->backendUrlBuilder = $backendUrlBuilder;
        parent::__construct($context, $data);
    }

    public function getSelectedOrderStatus() : array
    {
        return explode(',' , $this->configs->getSelectedOrderStatus());
    }

    public function getSelectedProductsTypes(): array
    {
        $selectedProductTypes = $this->configs->getSelectedProductsTypes()
                ?? $this->orderedProductTypes->getAllProductTypes();
        return explode(',', $selectedProductTypes);
    }
    
    public function getSelectedOrderAttributesLabels(): array
    {
        return $this->configs->getSelectedOrderAttributesLabels();        
    }

    public function getMessage(): string
    {
        return $this->ftpConnection->getConnFailureReason();
    }

    public function getOrdersDetails()
    {
        $collection = $this->orderCollectionFactory->create()->getData();
        return $collection;
    }

    public function getOrderData(int $orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        return $order;
    }

    public function getOrdersData()
    {
        try {
            $orders = $this->orderRepository;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        return $orders;
    }    

    public function displayMultiselectConfigs()
    {
        return $this->configs->getMultiselectValues();
    }
    
     /**
     * Generate URL for the admin configuration page
     *
     * @return string
     */
    public function getExportActionPath(): string
    {        
        return Configs::EXPORT_ACTION_PATH;      
       
    }
    
    /**
     * Generate URL for the admin configuration page
     *
     * @return string
     */
    public function getConfigsPath(): string
    {        
        return Configs::FTP_CONFIGS_PATH;      
       
    }
}
