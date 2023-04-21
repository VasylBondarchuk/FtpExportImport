<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Display;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;

class CsvExport
{
    private $driverFile;
    private $ordersDetails;
    private $csvProcessor;
    private $directoryList;
    private $logger;
    private $csvCreationFailureReason;
    private $csvExportFailureEmail;

    public function __construct(
        File $driverFile,
        OrdersDetails $ordersDetails,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        CsvExportFailureEmail $csvExportFailureEmail,
        Context $context
    ) {
        $this->driverFile = $driverFile;
        $this->ordersDetails = $ordersDetails;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->logger = $context->getLogger();
        $this->csvExportFailureEmail = $csvExportFailureEmail;
    }

    public function getCsvName()
    {
        return 'export_order_'.date("Y-m-d H:i:s").'.csv';
    }

    public function getCsvPath()
    {
        return $this->directoryList->getPath(DirectoryList::TMP) . DS . $this->getCsvName();
    }

    public function getCsvContent() : array
    {
        // csv header
        $content[] = [
            'order_id' => __('Order ID'),
            'status' => __('Status'),
            'total' => __('Total')
        ];
        foreach ($this->ordersDetails->getSelectedOrdersDetails() as $order) {
            $content[] = [
                $order->getId(),
                $order->getStatus(),
                $order->getBaseGrandTotal()
            ];
        }
        return $content;
    }

    public function appendDataToCsv(Csv $csvProcessor, string $csvPath, array $csvContent = []) : Csv
    {
        try {
            $csvProcessor->appendData($csvPath, $csvContent);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $this->csvCreationFailureReason=$e->getMessage();
        }
        return $csvProcessor;
    }

    public function createCsvFile()
    {
        $this->csvProcessor->setEnclosure('"')->setDelimiter(',');
        $this->appendDataToCsv($this->csvProcessor, $this->getCsvPath(), $this->getCsvContent());
        return $this->getCsvPath();
    }

    public function getCsvCreationFailureReason()
    {
        return $this->csvCreationFailureReason;
    }

    public function sendCsvCreationFailureEmail()
    {
        $this->csvExportFailureEmail->sendCsvCreationFailureEmail($this->getCsvCreationFailureReason());
    }
}