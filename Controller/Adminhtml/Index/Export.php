<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Message\ManagerInterface;
use Training\FtpExportImport\Model\FtpConnection;
use Training\FtpExportImport\Model\CsvExport;

class Export extends \Magento\Backend\App\Action
{
    /**
     * 
     * @var PageFactory
     */
    protected $resultPageFactory = false;
    /**
     * 
     * @var File
     */
    private File $driverFile;
    /**
     * 
     * @var CsvExport
     */
    private CsvExport $csvExport;
    /**
     * 
     * @var FtpConnection
     */
    private FtpConnection $ftpConnection; 
    /**
     * 
     * @var Ftp
     */
    private Ftp $ftp;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        PageFactory $resultPageFactory,
        File $driverFile,
        FtpConnection $ftpConnection,
        Context $context,
        CsvExport $csvExport,
        Ftp $ftp,
        ManagerInterface $messageManager,
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->driverFile = $driverFile;
        $this->ftpConnection = $ftpConnection;
        $this->csvExport = $csvExport;
        $this->ftp = $ftp;
        $this->messageManager = $messageManager;

        parent::__construct($context);
    }

    public function exportOrders()
    {
        // check if csv file to be exported was created
        try {
            $fileName = $this->csvExport->getCsvName();
            $filePath = $this->csvExport->createCsvFile();
            $content = $this->driverFile->fileGetContents($filePath);

        } catch (\Exception $e) {
            $this->csvExport->sendCsvCreationFailureEmail();            
            return;
        }

        // check if ftp connection was successful
        if (!$this->ftpConnection->isConnSuccessful()) {
            $this->ftpConnection->sendFtpConnFailureEmail();
            $this->messageManager->addErrorMessage(__('FTP connection failed. Possible reason: %1', $this->ftpConnection->getConnFailureReason()));
            return;
        }

        $this->ftp->write($fileName, $content);        
        $this->ftp->close();
    }

    public function execute()
    {
        $this->exportOrders();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Export to FTP'));
        
        return $resultPage;
    }
}
