<?php
declare(strict_types=1);

namespace Training\FtpOrderExport\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
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
     * @var ResultFactory
     */
    protected $resultFactory;

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
            ResultFactory $resultFactory,
            File $driverFile,
            FtpConnection $ftpConnection,
            Context $context,
            CsvExport $csvExport,
            Ftp $ftp,
            ManagerInterface $messageManager,
            Configs $configs,
            BackendUrlInterface $backendUrlBuilder
    ) {
        $this->resultFactory = $resultFactory;
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
                'addRedirectToSettingsMessage',['url' => $this->adminConfigUrl()]
            );
        } else {
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
            $this->exportFileToFtp($fileName, $content);
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }

    /**
     * 
     * @param string $fileName
     * @param string $content
     * @return void
     * 
     * Export file to FTP server
     */
    private function exportFileToFtp(string $fileName, string $content) : void {        
        if (!$this->ftpConnection->isConnSuccessful()) {
            $this->ftpConnection->sendFtpConnFailureEmail();
            $this->messageManager->addErrorMessage(
                    __('FTP connection failed. Possible reason: %1',
                            $this->ftpConnection->getConnFailureReason()));
        } else {
            $this->ftp->write($fileName, $content);
            $this->ftp->close();
            $this->messageManager->addSuccessMessage(
                    __('Orders were succsefully exported.')
            );
        }
    }

    /**
     * Returns URL for the admin configuration page
     *
     * @return string
     */
    private function adminConfigUrl(): string {
        return $this->backendUrlBuilder->getUrl(Configs::FTP_CONFIGS_PATH);
    }
}
