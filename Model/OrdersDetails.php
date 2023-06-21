<?php
declare(strict_types = 1);

namespace Training\FtpOrderExport\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Framework\Option\ArrayInterface;

class OrdersDetails implements ArrayInterface
{
    const ORDER_STATUSES = [
        Order::STATE_NEW,
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE,
        Order::STATE_CLOSED,
        Order::STATE_CANCELED,
        Order::STATE_HOLDED,
    ];
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
    private Configs $configs;
    /**
     * 
     * @var OrderInterface
     */
    private OrderInterface $order;
    
    /**
     * 
     * @var OrderConfig
     */
    private OrderConfig $orderConfig;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Configs $configs,
        OrderInterface $order,
        OrderConfig $orderConfig    
    )
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->configs = $configs;
        $this->order = $order;
        $this->orderConfig = $orderConfig;
    }

    /**
     * Returns orders matching selected statuses
     * 
     * @return Collection
     */
    public function getSelectedOrders() : Collection
    {
        $selectedOrders = $this->configs->getSelectedOrderStatus()
                ?? $this->getAllStatuses();
        return $this->orderCollectionFactory->create()
            ->addAttributeToFilter('status', ['in' => $selectedOrders])
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
    
    public function toOptionArray() : array
    {
        $statuses = self::ORDER_STATUSES
            ? $this->orderConfig->getStateStatuses(self::ORDER_STATUSES)
            : $this->orderConfig->getStatuses();

        $options = [['value' => '', 'label' => '']];

        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }

    public function getAllStatuses() : string
    {
        $statuses = '';

        foreach ($this->toOptionArray() as $items) {
            foreach ($items as $key => $value) {
                if ($key == 'value') {
                    $statuses .= $value . ',';
                }
            }
        }
        return trim($statuses, ',');
    }
}
