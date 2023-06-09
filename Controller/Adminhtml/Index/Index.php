<?php
declare(strict_types=1);

namespace Training\FtpOrderExport\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

class Index extends \Magento\Backend\App\Action {

    /**
     * 
     * @var PageFactory
     */
    protected $resultPageFactory = false;
    
    public function __construct(
            PageFactory $resultPageFactory,
            Context $context            
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {       
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Export Orders to FTP Server'));
        return $resultPage;
    }   
    
}
