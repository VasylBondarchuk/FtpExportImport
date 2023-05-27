<?php

declare(strict_types=1);

namespace Training\FtpExportImport\Model;

use \Magento\Framework\Filesystem\Driver\File;
use \Magento\Framework\File\Csv;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\InventoryApi\Api\SourceRepositoryInterface;

class CsvReader {

    private $file;
    private $csv;
    private $productRepository;

    public function __construct(
            File $file,
            Csv $csv,
            ProductRepository $productRepository,
            SourceRepositoryInterface $sourceRepository
    ) {
        $this->file = $file;
        $this->csv = $csv;
        $this->productRepository = $productRepository;
        $this->sourceRepository = $sourceRepository;
    }

    public function csvReaderGenerator($filePath, $delimeter = ',') {
        $batchSize = 100;
        $batch = [];
        $csvHeader = [];
        $batchCounter = 0;
        $rowCounter = 0;
        $handle = fopen($filePath, "r");

        if ($handle === false) {
            return false;
        }

        while (($data = fgetcsv($handle, $delimeter)) !== false) {
            if (0 == $rowCounter) {
                $csvHeader = $data;
            }
            if (0 !== $rowCounter) {
                $batch = array_combine($csvHeader, $data);
            }
            if (count($data) !== count($csvHeader))
                continue;
            $rowCounter++;
            // get a batch
            if (++$batchCounter == $batchSize) {
                yield $batch;
                $batch = [];
                $batchCounter = 0;
            }
            // return a residue of csv file data if any
            if (count($batch) > 0) {
                yield $batch;
            }
        }
        fclose($handle);
    }

}
