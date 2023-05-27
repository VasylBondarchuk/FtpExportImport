<?php
declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\Ftp;
use Training\FtpExportImport\Model\FtpConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Training\FtpExportImport\Model\CsvValidator;

class Import extends \Magento\Backend\App\Action
{
    private $resultPageFactory = false;
    private FtpConnection $ftpConnection;
    private Ftp $ftp;       
    private SourceItemInterfaceFactory $sourceItemFactory;
    private CsvValidator $csvValidator;

    public function __construct(
        PageFactory $resultPageFactory,
        Context $context,
        Ftp $ftp,
        FtpConnection $ftpConnection,        
        SourceItemInterfaceFactory $sourceItemFactory,
        CsvValidator $csvValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ftp = $ftp;
        $this->ftpConnection= $ftpConnection;        
        $this->sourceItemFactory  = $sourceItemFactory;
        $this->csvValidator = $csvValidator;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->importCsvFileFromFtp();
        $this->sendCsvDataToDb($this->csvValidator->getValidatedCsvData());
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import from FTP'));
        return $resultPage;
    }
    
    public function importCsvFileFromFtp()
    {
        $localCsvFilePath = BP. DIRECTORY_SEPARATOR .'pub' . DIRECTORY_SEPARATOR .'media'. DIRECTORY_SEPARATOR . 'import'. DIRECTORY_SEPARATOR .'local_import.csv';
        $ftpFilePath = 'import.csv';
        // make a connection
        if (!$this->ftpConnection->isConnSuccessful()) {
            $this->ftpConnection->sendFtpConnFailureEmail();
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
            $this->setQtyToProduct($row['Sku'], (float)$row['Qty'], $row['Source']);
        }
    }
}
