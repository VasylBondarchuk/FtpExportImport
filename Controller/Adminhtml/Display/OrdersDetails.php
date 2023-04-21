<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Display;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Sales\Model\Order;

class OrdersDetails
{
    private $orderCollectionFactory;
    private $orderRepository;
    private $configs;
    private $order;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Configs $configs,
        Order $order
    )
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->configs = $configs;
        $this->order = $order;
    }

    public function getSelectedOrdersDetails()
    {
        return $this->orderCollectionFactory->create()
            ->addAttributeToFilter('status', ['in' => $this->configs->getSelectedOrderStatus()])
            ->addAttributeToFilter('entity_id', ['in' => $this->getSelectedOrdersIds()]);
    }

    public function getAllOrdersIds(): array
    {
        $allOrdersIds = [];
        $allOrders = $this->orderCollectionFactory->create()->getData();

        foreach ($allOrders as $order => $item) {
            $allOrdersIds[] = $item['entity_id'];
        }
        return $allOrdersIds;
    }

    public function getOrderDataById(string $orderId)
    {
        try {
            $orderData = $this->orderRepository->get($orderId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This order no longer exists.'));
        }
        return $orderData;
    }

    // Returns true if the order contains at least one product of the selected type
    public function isProductTypeInOrder($order): bool
    {
        $orderItems = $order->getItemsCollection($this->configs->getSelectedProductsTypes(), true);

        foreach ($orderItems as $orderItem) {
            return true;
        }
        return false;
    }

    // Get ids of all orders ids, containing selected product types
    public function getSelectedOrdersIds(): array
    {
        $selectedOrdersIds = [];
        foreach ($this->getAllOrdersIds() as $orderId) {
            if ($this->isProductTypeInOrder($this->getOrderDataById($orderId))) {
                $selectedOrdersIds[] = $orderId;
            }
        }
        return $selectedOrdersIds;
    }
}
