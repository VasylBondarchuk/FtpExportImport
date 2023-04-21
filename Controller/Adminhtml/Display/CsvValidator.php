<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Controller\Adminhtml\Display;

use \Magento\Framework\Filesystem\Driver\File;
use \Magento\Framework\File\Csv;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\InventoryApi\Api\SourceRepositoryInterface;

class CsvValidator
{
    private $file;
    private $csv;
    private $csvReader;
    private $productRepository;

    public function __construct(
        File $file,
        Csv $csv,
        ProductRepository $productRepository,
        SourceRepositoryInterface $sourceRepository,
        CsvReader $csvReader
    )
    {
        $this->file = $file;
        $this->csv = $csv;
        $this->productRepository = $productRepository;
        $this->sourceRepository = $sourceRepository;
        $this->csvReader = $csvReader;
    }

    public function getCsvHeader(string $filePath) : array
    {
        $csvDataArray = array_map('str_getcsv', file($filePath));
        return $csvDataArray[0];
    }

    public function getRowLength(string $filePath) : array
    {
        $rowLength = [];
        $csvDataArray = array_map('str_getcsv', file($filePath));
        foreach($csvDataArray as $row){
            $rowLength[] = count($row);
        }
        return $rowLength;
    }

    public function getDirectCsvRawData(string $filePath) : array
    {
        $csvDataArray = array_map('str_getcsv', file($filePath));
        return $csvDataArray;
    }

    public function isCsvHeaderCorrect($filePath): bool
    {
        $expectedCsvHeader = ["Sku","Qty","Source"];
        return($this->getCsvHeader($filePath) === $expectedCsvHeader);
    }

    public function getRawCsvData(): array
    {
        $filePath = BP. DS .'pub' . DS .'media'. DS . 'import'. DS .'local_import.csv';
        $data = [];

        if(!$this->isCsvHeaderCorrect($filePath)) return $data;

        try {
            if ($this->file->isExists($filePath)) {
                foreach ($this->csvReader->csvReaderGenerator($filePath) as $row) {
                    $data[] = $row;
                }
                return $data;
            } else {
                //$this->logger->info('csv file does not exist');
                return false;
            }
        } catch (FileSystemException $e) {
            //$this->logger->info($e->getMessage());
            return false;
        }
    }

    public function getRowParam(array $row, string $columnName): string
    {
        return $row[$columnName];
    }

    public function getSourcesCodes() : array
    {
        $sourceData = $this->sourceRepository->getList()->getItems();
        $source_codes = [];
        foreach ($sourceData as $source) {
            $source_codes[] = $source['source_code'];
        }
        return $source_codes;
    }

    public function isQtyCorrect(array $row): bool
    {
        return is_numeric($this->getRowParam($row,"Qty"));
    }

    public function isSourceCodeCorrect($row): bool
    {
        return in_array($this->getRowParam($row,"Source"),$this->getSourcesCodes());
    }

    public function isRowCorrect(array $row): bool
    {
        if(!$this->isQtyCorrect($row)) return false;
        if(!$this->isSourceCodeCorrect($row)) return false;
        return true;
    }

    public function getValidatedCsvData(): array
    {
        return array_filter($this->getRawCsvData(), function ($row) {
            if ($this->isRowCorrect($row)) {
                return $row;
            }
        });
    }


    /*public function csvLogger(): string
    {
        $loggerMessages =[];
        $linesQty = count($this->getRawCsvData());

        for($i = 0; $i < $linesQty; $i++){
            $row = $this->getRawCsvData()[$i];
            $lineNumber = $i + 2;

            if(!$this->isRowCorrect($row)){
                $incorrectSkuMessage = !$this->isSkuCorrect($this->getSku($row))
                    ? "Sku value in this row is not valid. " : "";
                $incorrectQtyMessage = !$this->isQtyCorrect($this->getQty($row))
                    ? "Qty value in this row is not valid. " : "";
                $loggerMessages[] = "Line $lineNumber of your file was not taken into account. ".
                    $incorrectSkuMessage . $incorrectQtyMessage;
            }
        }
        return implode("<br>",$loggerMessages);
    }

    public function getProductBySku(?string $sku)
    {
        try {
            $product = $sku ? $this->productRepository->get($sku) : null;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = null;
        }

        return $product;
    }

    public function getQtysBySku(string $sku): array
    {
        $qtyBySku = [];
        foreach ($this->getValidatedCsvData() as $row) {
            if ($this->getSku($row) == $sku) {
                array_push($qtyBySku,$this->getQty($row));
            }
        }
        return $qtyBySku;
    }

    public function getUniqueSkus(): array
    {
        return array_unique(array_map(function ($row) {
            return $this->getSku($row);}, $this->getValidatedCsvData()));
    }

    // Get sum of qtys of the rows with the indentical sku
    public function getProcessedCsvData(): array
    {
        $processedCsvData = [];
        foreach ($this->getUniqueSkus() as $sku) {
            $processedCsvData[$sku] = array_sum($this->getQtysBySku($sku));
        }
        return $processedCsvData;
    }


    public function isSkuCorrect(string $sku): bool
    {
        return $this->getProductBySku($sku) ? true : false;
    }


    */

}
