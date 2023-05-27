<?php
declare(strict_types = 1);

namespace Training\FtpExportImport\Model;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;

class CsvExport
{
    /**
     * 
     * @var File
     */
    private File $file;
    /**
     * 
     * @var OrdersDetails
     */
    private OrdersDetails $ordersDetails;
    /**
     * 
     * @var Csv
     */
    private Csv $csvProcessor;
    /**
     * 
     * @var DirectoryList
     */
    private DirectoryList $directoryList;
    /**
     * 
     * @var type
     */
    private $logger;
    /**
     * 
     * @var type
     */
    private $csvCreationFailureReason;
    /**
     * 
     * @var type
     */
    private $csvExportFailureEmail;

    public function __construct(
        File $file,
        OrdersDetails $ordersDetails,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        CsvExportFailureEmail $csvExportFailureEmail,
        Context $context
    ) {
        $this->file = $file;
        $this->ordersDetails = $ordersDetails;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->logger = $context->getLogger();
        $this->csvExportFailureEmail = $csvExportFailureEmail;
    }

    /**
     * 
     * @return string
     */    
    public function getCsvName(): string
    {
        return 'export_order_'.date("Y-m-d-H:i:s").'.csv';
    }

    /**
     * 
     * @return string
     */
    public function getCsvPath() : string
    {
        return $this->directoryList->getPath(DirectoryList::TMP) . DIRECTORY_SEPARATOR . $this->getCsvName();
    }

    /**
     * 
     * @return array
     */
    public function getCsvContent() : array
    {
        // csv header
        $content[] = [
            'order_id' => __('Order ID'),
            'status' => __('Status'),
            'total' => __('Total')
        ];
        foreach ($this->ordersDetails->getSelectedOrders() as $order) {
            $content[] = [
                $order->getId(),
                $order->getData('status'),
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

    public function createCsvFile() : string
    {
        $this->csvProcessor->setEnclosure('"')->setDelimiter(',');
        $this->appendDataToCsv($this->csvProcessor, $this->getCsvPath(), $this->getCsvContent());
        return $this->getCsvPath();
    }

    public function getCsvCreationFailureReason() : ?string
    {
        return $this->csvCreationFailureReason;
    }

    public function sendCsvCreationFailureEmail() : void
    {
        $this->csvExportFailureEmail->sendCsvCreationFailureEmail($this->getCsvCreationFailureReason());
    }
}