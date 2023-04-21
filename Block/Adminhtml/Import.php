<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Training\FtpExportImport\Controller\Adminhtml\Display\CsvValidator;
use \Magento\InventoryApi\Api\SourceRepositoryInterface;

class Import extends Template
{
    private $csvValidator;
    private $sourceRepository;

    public function __construct(
        Context $context,
        CsvValidator $csvValidator,
        SourceRepositoryInterface $sourceRepository,
        array $data = []
    )
    {
        $this->csvValidator = $csvValidator;
        $this->sourceRepository = $sourceRepository;
        parent::__construct($context, $data);
    }

    public function getValidatedCsvData($CsvData)
    {
        return $this->csvValidator->getValidatedCsvData($CsvData);
    }

    public function getRawCsvData()
    {
        return $this->csvValidator->getRawCsvData();
    }

    public function checkExistingSku(string $inputSku){
        return $this->csvValidator->getProductBySku($inputSku);
    }

    public function getUniqueSku(array $csvData){
        return $this->csvValidator->getUniqueSku($csvData);
    }

    public function getSkuQty(array $csvData){
        return $this->csvValidator->getSkuQty($csvData);
    }

    public function csvLogger(){
        return $this->csvValidator->csvLogger();
    }

    /*public function getProcessedCsvData(){
        return $this->csvValidator->getProcessedCsvData();
    }*/

    public function memoryUsage(){
        return $this->csvValidator->memoryUsage();
    }

    public function getSourceList()
    {
        $sourceData = $this->sourceRepository->getList();
        return $sourceData->getItems();
    }

    public function getCsvHeader($filePath)
    {
        return $this->csvValidator->getCsvHeader($filePath);
    }

    public function getRowLength($filePath)
    {
        return $this->csvValidator->getRowLength($filePath);
    }

    public function getDirectCsvRawData($filePath)
    {
        return $this->csvValidator->getDirectCsvRawData($filePath);
    }

}

