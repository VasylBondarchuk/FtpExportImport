<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Display;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\Ftp;

class Export extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $driverFile;
    protected $csvProcessor;
    protected $ftpConnection;
    protected $ftp;

    public function __construct(
        PageFactory $resultPageFactory,
        File $driverFile,
        FtpConnection $ftpConn,
        Context $context,
        CsvExport $csvExport,
        Ftp $ftp
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->driverFile = $driverFile;
        $this->ftpConn = $ftpConn;
        $this->csvExport = $csvExport;
        $this->ftp = $ftp;

        parent::__construct($context);
    }

    public function exportOrders()
    {
        // check if csv file was created
        try {
            $fileName = $this->csvExport->getCsvName();
            $filePath = $this->csvExport->createCsvFile();
            $content = $this->driverFile->fileGetContents($filePath);

        } catch (\Exception $e) {
            $this->csvExport->sendCsvCreationFailureEmail();
            return;
        }

        // check if ftp connection was successful

        if (!$this->ftpConn->isConnSuccessful()) {
            $this->ftpConn->sendFtpConnFailureEmail();
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
