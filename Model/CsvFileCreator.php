<?php
declare(strict_types=1);

namespace Training\FtpOrderExport\Model;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;

class CsvFileCreator {

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
    private FailureEmailDetails $failureEmailDetails;

    public function __construct(
            File $file,
            OrdersDetails $ordersDetails,
            Csv $csvProcessor,
            DirectoryList $directoryList,
            FailureEmailDetails $failureEmailDetails,
            Context $context
    ) {
        $this->file = $file;
        $this->ordersDetails = $ordersDetails;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->logger = $context->getLogger();
        $this->failureEmailDetails = $failureEmailDetails;
    }

    /**
     * 
     * @return string
     */
    public function getCsvName(): string {
        return 'export_orders_' . date("Y-m-d-H:i:s") . '.csv';
    }

    /**
     * 
     * @return string
     */
    private function getCsvPath(): string {
        return $this->directoryList->getPath(DirectoryList::TMP)
                . DIRECTORY_SEPARATOR . $this->getCsvName();
    }

    private function getCsvHeader() {
        $header = [];
        foreach ($this->ordersDetails->getSelectedOrderAttributes() as $attribute) {
            $header[] = __($this->formatString($attribute));
        }
        return $header;
    }

    private function getCsvLine($order): array {
        $line = [];
        foreach ($this->ordersDetails->getSelectedOrderAttributes() as $attribute) {
            $line[] = $order->getData($attribute);
        }
        return $line;
    }

    private function formatString($inputString) {
        $replacedString = str_replace('_', ' ', $inputString);
        $formattedString = ucwords($replacedString);
        return $formattedString;
    }

    /**
     * 
     * @return array
     */
    public function getCsvContent(): array {
        // csv header
        $content[] = $this->getCsvHeader();
        foreach ($this->ordersDetails->getSelectedOrders() as $order) {
            $content[] = $this->getCsvLine($order);
        }
        return $content;
    }

    /**
     * 
     * @param Csv $csvProcessor
     * @param string $csvPath
     * @param array $csvContent
     * @return Csv
     */
    public function appendDataToCsv(
            Csv $csvProcessor,
            string $csvPath,
            array $csvContent = []): Csv {
        try {
            $csvProcessor->appendData($csvPath, $csvContent);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $this->csvCreationFailureReason = $e->getMessage();
        }
        return $csvProcessor;
    }

    /**
     * 
     * @return string
     */
    public function createCsvFile(): string {
        $this->csvProcessor->setEnclosure('"')->setDelimiter(',');
        $this->appendDataToCsv($this->csvProcessor, $this->getCsvPath(), $this->getCsvContent());
        return $this->getCsvPath();
    }

    /**
     * 
     * @return string|null
     */
    public function getCsvCreationFailureReason(): ?string {
        return $this->csvCreationFailureReason;
    }

    /**
     * 
     * @return void
     */
    public function sendCsvCreationFailureEmail(): void {
        $this->failureEmailDetails->sendFailureEmail
                (
                $this->failureEmailDetails->getSenderDetails(["TSG", "office@transoftgroup.com"]),
                $this->failureEmailDetails->getRecipientEmail('office@transoftgroup.com'),
                $this->failureEmailDetails->getTemplateIdentifier('email_csv_creation_failure_template'),
                $this->failureEmailDetails->getTemplateOptions(),
                $this->failureEmailDetails->getTemplateVars(
                        [
                            'Developer',
                            $this->failureEmailDetails->getLink(Configs::FTP_CONFIGS_PATH),
                            "TSG",
                            $this->getCsvCreationFailureReason()
                        ]
                )
        );
    }
}
