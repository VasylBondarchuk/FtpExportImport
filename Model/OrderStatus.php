<?php

namespace Training\FtpExportImport\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Framework\Option\ArrayInterface;

class OrderStatus implements ArrayInterface
{
    const ORDER_STATUSES = [
        Order::STATE_NEW,
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE,
        Order::STATE_CLOSED,
        Order::STATE_CANCELED,
        Order::STATE_HOLDED,
    ];

    private $orderConfig;
    
    public function __construct(Config $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    public function toOptionArray()
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

    public function getAllStatuses()
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
