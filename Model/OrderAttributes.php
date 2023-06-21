<?php

declare(strict_types=1);

namespace Training\FtpOrderExport\Model;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\ResourceConnection;

class OrderAttributes implements OptionSourceInterface {

    protected ResourceConnection $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array {
        $orderAtributes = \Safe\array_combine(
                $this->getOrderAttributesNames(),
                $this->getOrderAttributesLabels()
        );
        $options = [];
        foreach ($orderAtributes as $orderAttributeValue => $orderAttributeLabel) {
            $options[] = ['value' => $orderAttributeValue, 'label' => $orderAttributeLabel];
        }
        return $options;
    }

    /**
     * 
     * @return array
     */
    public function getOrderAttributesNames(): array {
        $columnNames = [];
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('sales_order');
            $columns = $connection->describeTable($tableName);
            foreach ($columns as $column) {
                $columnNames[] = $column['COLUMN_NAME'];
            };
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return $columnNames;
    }

    private function formatString(string $inputString): string {
        $replacedString = str_replace('_', ' ', $inputString);
        $formattedString = ucwords($replacedString);
        return $formattedString;
    }

    public function getOrderAttributesLabels(): array {
        $orderAttributesLabels = [];
        foreach ($this->getOrderAttributesNames() as $name) {
            $orderAttributesLabels[] = $this->formatString($name);
        }
        return $orderAttributesLabels;
    }
}
