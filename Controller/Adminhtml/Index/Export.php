<?php
declare(strict_types=1);

namespace Training\FtpOrderExport\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Message\ManagerInterface;
use Training\FtpOrderExport\Model\FtpConnection;
use Training\FtpOrderExport\Model\CsvExport;
use Training\FtpOrderExport\Model\Configs;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;

class Export extends \Magento\Backend\App\Action {

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

    /**
     * 
     * @var Configs
     */
    private Configs $configs;
    

    /**
     * @var BackendUrlInterface
     */
    private $backendUrlBuilder;

    public function __construct(
            PageFactory $resultPageFactory,
            File $driverFile,
            FtpConnection $ftpConnection,
            Context $context,
            CsvExport $csvExport,
            Ftp $ftp,
            ManagerInterface $messageManager,
            Configs $configs,            
            BackendUrlInterface $backendUrlBuilder
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->driverFile = $driverFile;
        $this->ftpConnection = $ftpConnection;
        $this->csvExport = $csvExport;
        $this->ftp = $ftp;
        $this->messageManager = $messageManager;
        $this->configs = $configs;        
        $this->backendUrlBuilder = $backendUrlBuilder;

        parent::__construct($context);
    }

    public function execute() {
        if (!$this->configs->isExportEnabled()) {
            $this->messageManager->addComplexErrorMessage(
                    'addRedirectToSettingsMessage',
                    [
                        'url' => $this->generateAdminConfigUrl()
                    ]
            );            
        }        
        else {
            // check if csv file to be exported was created
            try {
                $fileName = $this->csvExport->getCsvName();
                $filePath = $this->csvExport->createCsvFile();
                $content = $this->driverFile->fileGetContents($filePath);
            } catch (\Exception $e) {
                $this->csvExport->sendCsvCreationFailureEmail();
                $this->messageManager->addErrorMessage(
                        __('Csv file to be exported was not created. Possible reason: %1', $e->getMessage()));
            }
            // check if ftp connection was successful
            if (!$this->ftpConnection->isConnSuccessful()) {
                $this->ftpConnection->sendFtpConnFailureEmail();
                $this->messageManager->addErrorMessage(
                        __('FTP connection failed. Possible reason: %1', $this->ftpConnection->getConnFailureReason()));
            } else {                
                $this->ftp->write($fileName, $content);
                $this->ftp->close();
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Export Orders to FTP Server'));
        return $resultPage;
    }
    
    /**
     * Generate URL for the admin configuration page
     *
     * @return string
     */
    public function generateAdminConfigUrl(): string
    {        
        return $this->backendUrlBuilder->getUrl(Configs::FTP_CONFIGS_PATH);       
       
    }
}
