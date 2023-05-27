<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class OrdersDetails
{
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
     * @var type
     */
    private $configs;
    /**
     * 
     * @var OrderInterface
     */
    private OrderInterface $order;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Configs $configs,
        OrderInterface $order
    )
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->configs = $configs;
        $this->order = $order;
    }

    /**
     * 
     * @return Collection
     */
    public function getSelectedOrders() : Collection
    {
        return $this->orderCollectionFactory->create()
            ->addAttributeToFilter('status', ['in' => $this->configs->getSelectedOrderStatus()])
            ->addAttributeToFilter('entity_id', ['in' => $this->getSelectedOrdersIds()]);
    }

    /**
     * 
     * @return array
     */
    public function getAllOrdersIds(): array
    {
        $orderIds = [];
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToSelect('entity_id');
        foreach ($orderCollection as $order) {
            $orderIds[] = $order->getId();
        }
        return $orderIds;
    }
           
    // Check if the order contains selected product types
    public function isProductTypeInOrder(OrderInterface $order): bool
    {        
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            if (in_array($item->getProductType(), explode(',', $this->configs->getSelectedProductsTypes()))) {
                return true;
            }
        }
        return false;
    }    

    // Get id's of orders, containing selected product types
    public function getSelectedOrdersIds(): array
    {
        $selectedOrdersIds = [];
        foreach ($this->getAllOrdersIds() as $orderId) {
            if ($this->isProductTypeInOrder($this->orderRepository->get($orderId))) {
                $selectedOrdersIds[] = $orderId;
            }
        }
        return $selectedOrdersIds;
    }
}
