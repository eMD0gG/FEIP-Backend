<?php
namespace App\Service;

class CsvService{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function readCsv(string $filename): array
    {
        $file = fopen($this->projectDir . '/assets/csv/' . $filename, 'r');
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
        $file = fopen($this->projectDir . '/assets/csv/' . $filename, 'w');
        fputcsv($file, $header);

        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
    }
}