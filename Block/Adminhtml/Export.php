<?php

declare(strict_types = 1);

namespace Training\FtpOrderExport\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Training\FtpOrderExport\Model\FtpConnection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Training\FtpOrderExport\Controller\Adminhtml\Index\Configs;
use Training\FtpOrderExport\Model\OrderStatus;
use Training\FtpOrderExport\Model\OrderedProductTypes;

class Export extends Template
{
    private $ftpConnection;
    protected $orderCollectionFactory;
    protected $orderRepository;
    protected $configs;
    protected $orderStatuses;
    private $OrderedProductTypes;

    public function __construct(
        Context $context,
        FtpConnection $ftpConnection,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Configs $configs,
        OrderStatus $orderStatuses,
        OrderedProductTypes $OrderedProductTypes,
        array $data = []
    ) {
        $this->ftpConnection = $ftpConnection;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->configs = $configs;
        $this->orderStatuses = $orderStatuses;
        $this->OrderedProductTypes = $OrderedProductTypes;

        parent::__construct($context, $data);
    }

    public function getAllOrderStatuses()
    {
        return $this->configs->getSelectedOrderStatus();
    }

    public function getAllOrderedProductTypes()
    {
        return $this->configs->getSelectedProductsTypes();
    }

    public function getMessage()
    {
        return $this->ftpConnection->getConnFailureReason();
    }

    public function getOrdersDetails()
    {
        $collection = $this->orderCollectionFactory->create()->getData();

        return $collection;
    }

    public function getOrderData($order_id)
    {
        try {
            $order = $this->orderRepository->get($order_id);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This order no longer exists.'));
        }
        return $order;
    }

    public function getOrdersData()
    {
        try {
            $orders = $this->orderRepository;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This order no longer exists.'));
        }
        return $orders;
    }

    public function getProductsData(\Magento\Sales\Model\Order $order): bool
    {
        $orderItems = $order->getItemsCollection(['bundle'], true);

        foreach ($orderItems as $orderItem) {
            return true;
        }
        return false;
    }

    public function displayMultiselectConfigs()
    {
        return $this->configs->getMultiselectValues();
    }
}
