<?php

namespace App\Service;

class CsvService{
    private string $csvDirectory;

    public function __construct(string $csvDirectory)
    {
        $this->csvDirectory = $csvDirectory; 
    }

    public function readCsv(string $filename): array
    {
        $file = fopen($this->csvDirectory . $filename, 'r');
        $header = fgetcsv($file);
        $data = [];

        while (($row = fgetcsv($file)) !== false) {
            $data[] = array_combine($header, $row);
        }

        fclose($file);

        return $data;
    }

    public function writeCsv(string $filename, array $data, array $header): void
    {
        $file = fopen($this->csvDirectory . '/assets/csv/' . $filename, 'w');
        fputcsv($file, $header);

        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
    }
}
