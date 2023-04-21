<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Display;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\Ftp;
use Training\FtpExportImport\Controller\Adminhtml\Display\FtpConnection;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Training\FtpExportImport\Controller\Adminhtml\Display\CsvValidator;

class Import extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $ftpConnection;
    protected $ftp;
    protected $sourceItemsSave;
    protected $sourceItemFactory;
    protected $csvValidator;

    public function __construct(
        PageFactory $resultPageFactory,
        Context $context,
        Ftp $ftp,
        FtpConnection $ftpConnection,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,
        CsvValidator $csvValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ftp = $ftp;
        $this->ftpConn= $ftpConnection;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory  = $sourceItemFactory;
        $this->csvValidator = $csvValidator;

        parent::__construct($context);
    }

    public function importCsvFileFromFtp()
    {
        $localCsvFilePath = BP. DS .'pub' . DS .'media'. DS . 'import'. DS .'local_import.csv';
        $ftpFilePath = 'import.csv';

        // make a connection
        if (!$this->ftpConn->isConnSuccessful()) {
            $this->ftpConn->sendFtpConnFailureEmail();
            return;
        }

        $this->ftp->read($ftpFilePath, $localCsvFilePath);
        $this->ftp->close();

        return $localCsvFilePath;
    }

    public function setQtyToProduct($sku, $qty, $source)
    {
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode($source);
        $sourceItem->setSku($sku);
        $sourceItem->setQuantity($qty);
        $sourceItem->setStatus(1);

        $this->sourceItemsSave->execute([$sourceItem]);
    }

    public function sendCsvDataToDb(array $csvData)
    {
        foreach($csvData as $row)
        {
            $this->setQtyToProduct($row["Sku"], (float)$row["Qty"], $row["Source"]);
        }
    }

    public function execute()
    {
        $this->importCsvFileFromFtp();
        $this->sendCsvDataToDb($this->csvValidator->getValidatedCsvData());

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import from FTP'));

        return $resultPage;
    }
}
