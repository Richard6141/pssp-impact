<?php

namespace App\Services;

use App\Models\Site;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

class SiteImportService
{
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);
        if (empty($rows)) {
            return [
                'success_count' => 0,
                'error_count' => 1,
                'errors' => ['Le fichier est vide ou illisible.'],
            ];
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $successCount = 0;
        $errors = [];

        foreach ($rows as $index => $values) {
            $line = $index + 2;
            $row = $this->mapRow($headers, $values);

            $siteCode = strtoupper(trim((string) $this->pick($row, ['site_code', 'code_site', 'code'])));
            $siteName = trim((string) $this->pick($row, ['site_name', 'nom_site', 'nom']));
            $departement = trim((string) $this->pick($row, ['site_departement', 'departement']));
            $commune = trim((string) $this->pick($row, ['site_commune', 'commune']));
            $localisation = trim((string) $this->pick($row, ['localisation', 'adresse']));
            $latitude = $this->nullableNumber($this->pick($row, ['latitude']));
            $longitude = $this->nullableNumber($this->pick($row, ['longitude']));
            $responsableEmail = trim((string) $this->pick($row, ['responsable_email', 'email_responsable']));
            $responsableUsername = trim((string) $this->pick($row, ['responsable_username', 'username_responsable']));

            $payload = [
                'site_code' => $siteCode,
                'site_name' => $siteName,
                'site_departement' => $departement,
                'site_commune' => $commune,
                'localisation' => $localisation,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];

            $validator = Validator::make($payload, [
                'site_code' => 'required|string|max:20|unique:sites,site_code',
                'site_name' => 'required|string|max:255',
                'site_departement' => 'required|string|max:255',
                'site_commune' => 'required|string|max:255',
                'localisation' => 'required|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                $errors[] = "Ligne {$line}: " . implode(', ', $validator->errors()->all());
                continue;
            }

            $responsableId = null;
            if ($responsableEmail !== '') {
                $responsableId = User::where('email', $responsableEmail)->value('user_id');
                if (!$responsableId) {
                    $errors[] = "Ligne {$line}: responsable_email introuvable ({$responsableEmail}).";
                    continue;
                }
            } elseif ($responsableUsername !== '') {
                $responsableId = User::where('username', $responsableUsername)->value('user_id');
                if (!$responsableId) {
                    $errors[] = "Ligne {$line}: responsable_username introuvable ({$responsableUsername}).";
                    continue;
                }
            }

            Site::create([
                'site_code' => $payload['site_code'],
                'site_name' => $payload['site_name'],
                'site_departement' => $payload['site_departement'],
                'site_commune' => $payload['site_commune'],
                'localisation' => $payload['localisation'],
                'latitude' => $payload['latitude'],
                'longitude' => $payload['longitude'],
                'responsable' => $responsableId,
            ]);

            $successCount++;
        }

        return [
            'success_count' => $successCount,
            'error_count' => count($errors),
            'errors' => $errors,
        ];
    }

    private function readRows(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsvRows($file->getRealPath());
        }

        if ($extension === 'xlsx') {
            return $this->readXlsxRows($file->getRealPath());
        }

        return [];
    }

    private function readCsvRows(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) === false) {
            return $rows;
        }

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $rows[] = array_map(fn($v) => trim((string) $v), $row);
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return [];
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $cells = [];
            foreach ($rowNode->c as $c) {
                $ref = (string) $c['r'];
                preg_match('/^[A-Z]+/', $ref, $matches);
                $col = $matches[0] ?? 'A';
                $colIndex = $this->columnToIndex($col);

                $type = (string) $c['t'];
                $value = '';
                if ($type === 's') {
                    $sharedIndex = (int) ((string) $c->v);
                    $value = $sharedStrings[$sharedIndex] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $c->is->t;
                } else {
                    $value = (string) $c->v;
                }

                $cells[$colIndex] = trim($value);
            }

            if (empty($cells)) {
                continue;
            }

            ksort($cells);
            $max = max(array_keys($cells));
            $row = [];
            for ($i = 0; $i <= $max; $i++) {
                $row[] = $cells[$i] ?? '';
            }

            if ($this->isEmptyRow($row)) {
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xmlString = $zip->getFromName('xl/sharedStrings.xml');
        if (!$xmlString) {
            return [];
        }

        $xml = @simplexml_load_string($xmlString);
        if (!$xml || !isset($xml->si)) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $value = '';
            if (isset($si->r)) {
                foreach ($si->r as $r) {
                    $value .= (string) $r->t;
                }
            }
            $strings[] = $value;
        }

        return $strings;
    }

    private function columnToIndex(string $column): int
    {
        $column = strtoupper($column);
        $index = 0;
        $length = strlen($column);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = strtolower(trim((string) $header));
            $header = preg_replace('/[^a-z0-9]+/i', '_', $header);
            return trim($header, '_');
        }, $headers);
    }

    private function mapRow(array $headers, array $values): array
    {
        $row = [];
        foreach ($headers as $i => $header) {
            if ($header === '') {
                continue;
            }
            $row[$header] = isset($values[$i]) ? trim((string) $values[$i]) : null;
        }
        return $row;
    }

    private function pick(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }
        return null;
    }

    private function nullableNumber(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? (float) $value : null;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }
}
