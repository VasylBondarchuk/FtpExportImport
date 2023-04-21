<?php

namespace Training\FtpExportImport\Model;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_COMPLETE,
        \Magento\Sales\Model\Order::STATE_CLOSED,
        \Magento\Sales\Model\Order::STATE_CANCELED,
        \Magento\Sales\Model\Order::STATE_HOLDED,
    ];

    protected $_orderConfig;

    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig)
    {
        $this->_orderConfig = $orderConfig;
    }

    public function toOptionArray()
    {
        $statuses = $this->_stateStatuses
            ? $this->_orderConfig->getStateStatuses($this->_stateStatuses)
            : $this->_orderConfig->getStatuses();

        $options = [['value' => '', 'label' => '']];

        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }

    public function getAllStatuses()
    {
        $statuses = "";

        foreach ($this->toOptionArray() as $items) {
            foreach ($items as $key => $value) {
                if ($key=="value") {
                    $statuses .= $value.",";
                }
            }
        }
        return trim($statuses, ",");
    }
}
