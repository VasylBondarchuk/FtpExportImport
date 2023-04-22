<?php

namespace Training\FtpExportImport\Cron;

class Test
{
    protected $exportOrders;

    public function __construct(\Training\FtpExportImport\Controller\Adminhtml\Index\Export $exportOrders)
    {
        $this->exportOrders = $exportOrders;
    }

    public function execute()
    {
        $this->exportOrders->exportOrders();
    }
}
