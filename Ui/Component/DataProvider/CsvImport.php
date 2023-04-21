<?php

declare(strict_types = 1);

namespace Training\FtpExportImport\Ui\Component\DataProvider;

use \Magento\Ui\DataProvider\AbstractDataProvider;
use \Training\FtpExportImport\Controller\Adminhtml\Display\Import;
use \Magento\Framework\Filesystem\Driver\File;
use \Magento\Framework\File\Csv;
use Training\FtpExportImport\Controller\Adminhtml\Display\CsvValidator;

class CsvImport extends AbstractDataProvider
{

    private $import;
    private  $file;
    private  $csv;
    private  $csvValidator;

    public function __construct(
        Import $import,
        File $file,
        Csv $csv,
        CsvValidator $csvValidator,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->import = $import;
        $this->file = $file;
        $this->csv = $csv;
        $this->csvValidator = $csvValidator;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getCsvFileDataArray():array
    {
        foreach ($this->csvValidator->getValidatedCsvData() as $row) {
            $csvFileDataArray[] = [
                'sku' => $row['Sku'],
                'qty' => $row['Qty'],
                'source' => $row['Source']
            ];
        }
        return $csvFileDataArray;
    }

    public function getData()
    {
        $result = [
            'items' => $this->getCsvFileDataArray(),
            'totalRecords' => count($this->getCsvFileDataArray())
        ];
        return $result;
    }
}
