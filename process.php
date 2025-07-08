<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ===== Utility Functions =====
 */

function normalizeText(string $text): string {
    return trim(preg_replace('/\s+/', ' ', $text));
}

function extractTextOnly(string $inputLine): string {
    $cleaned = preg_replace('/\s*\d[\d.]*\s*/', ' ', $inputLine);
    return normalizeText($cleaned);
}

function extractBetween($text, $start, $end) {
    $pattern = '/' . preg_quote($start, '/') . '(.*?)' . preg_quote($end, '/') . '/s';
    return preg_match($pattern, $text, $matches) ? trim($matches[1]) : null;
}

/**
 * ===== Tax Data Extraction Functions =====
 */

function extractTaxValues($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $pattern = '/(\d{1,3}(?:\.\d{3})+)\s+(\d+)\s+(\d{1,3}(?:\.\d{3})+)/';

    foreach ($lines as $i => $line) {
        $line = trim($line);
        if (preg_match($pattern, $line, $m) || 
            (preg_match('/^\d{2}-\d{3}-\d{2}/', $line) &&
             preg_match($pattern, $line . ' ' . @$lines[$i+1] . ' ' . @$lines[$i+2], $m))) {
            return ['dpp' => $m[1], 'tarif' => $m[2], 'pph' => $m[3]];
        }
    }

    return ['dpp' => '', 'tarif' => '', 'pph' => ''];
}

function extractKodeAndObjekPajak($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $pattern = '/^(\d{2}-\d{3}-\d{2})\s+(.+)$/';
    $results = [];

    foreach ($lines as $i => $line) {
        if (preg_match($pattern, trim($line), $m)) {
            $desc = $m[2];
            $next = trim($lines[$i+1] ?? '');
            if (!preg_match('/^\d{1,3}(\.\d{3})*\s+\d+\s+\d{1,3}(\.\d{3})*/', $next)) {
                $desc .= ' ' . $next;
            }
            $results[] = ['b3' => $m[1], 'b4' => normalizeText($desc)];
        }
    }
    return $results;
}

/**
 * ===== Excel Export =====
 */

function exportToExcel($results, $filename = 'output.xlsx') {
    $sheet = (new Spreadsheet())->getActiveSheet();
    $headers = ['File', 'A.1 NPWP/NIK', 'A.2 NAMA', 'B.3 Kode Objek Pajak', 'B.4 Objek Pajak',
                'B.5 DPP (Rp)', 'B.6 TARIF (%)', 'B.7 PPH (Rp)', 'B.8 Jenis Dokumen', 
                'B.8 Tanggal Dokumen', 'B.9. Nomor Dokumen', 'C.1. NPWP/NIK', 'C.3. Nama Pemotong',
                'C.4. Tanggal', 'C.5. Nama Penandatangan'];
    $sheet->fromArray($headers, null, 'A1');

    foreach ($results as $i => $r) {
        $row = $i + 2;
        $sheet->fromArray(array_values($r), null, "A$row");
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    (new Xlsx($sheet->getParent()))->save('php://output');
    exit;
}

/**
 * ===== Main Processing Workflow =====
 */

if (!isset($_FILES['zip_file'])) {
    exit("Please upload a ZIP file containing PDFs.");
}

$tempDir = sys_get_temp_dir() . '/pdf_zip_' . uniqid();
mkdir($tempDir);

$zip = new ZipArchive();
if (!$zip->open($_FILES['zip_file']['tmp_name'])) {
    exit("Failed to open ZIP file.");
}
$zip->extractTo($tempDir);
$zip->close();

try {
    $parser = new Parser();
    $results = [];

    foreach (glob("$tempDir/*.pdf") as $pdfPath) {
    $text = $parser->parseFile($pdfPath)->getText();

    $a1 = extractBetween($text, 'A.1NPWP / NIK :', 'A.2');
    $a2 = extractBetween($text, 'A.2NAMA	:', 'A.3');

    $b3 = $b4 = '';
    foreach (extractKodeAndObjekPajak($text) as $item) {
        $b3 = $item['b3'];
        $b4 = extractTextOnly($item['b4']); // refined use
        break; // only first match used
    }

    $tax = extractTaxValues($text);
    $b8 = extractBetween($text, 'B.8', 'B.9');
    $b8 = explode(':', explode('Fasilitas', $b8)[1] ?? '');
    [$b8_jenis_dokumen, $b8_tanggal] = [trim($b8[1] ?? ''), trim($b8[2] ?? '')];

    $result = [
        'file' => basename($pdfPath),
        'a1' => $a1,
        'a2' => $a2,
        'b3' => $b3,
        'b4' => $b4,
        'b5' => '\''.$tax['dpp'],
        'b6' => '\''.$tax['tarif'],
        'b7' => '\''.$tax['pph'],
        'b8_jenis_dokumen' => $b8_jenis_dokumen,
        'b8_tanggal' => $b8_tanggal,
        'b9' => extractBetween($text, 'B.9 	Nomor Dokumen :', 'B.10'),
        'c1' => trim((explode(':', extractBetween($text, 'C.1', 'C.2'))[1] ?? '')),
        'c3' => trim((explode(':', extractBetween($text, 'C.3', 'C.4'))[1] ?? '')),
        'c4' => extractBetween($text, 'C.4TANGGAL	:', 'C.5'),
        'c5' => extractBetween($text, 'C.5NAMA PENANDATANGAN	:', 'C.6'),
    ];
        $results[] = $result;
    }

    exportToExcel($results, 'output_' . time() . '.xlsx');

} finally {
    // Ensure cleanup even if an error occurs
    array_map('unlink', glob("$tempDir/*.*"));
    @rmdir($tempDir);
}
