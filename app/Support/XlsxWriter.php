<?php

namespace App\Support;

use ZipArchive;

/**
 * Penulis file Excel (.xlsx) sederhana, hanya butuh ekstensi zip bawaan PHP.
 *
 * $x = new XlsxWriter();
 * $x->addSheet('Ringkasan', [
 *     [['v' => 'Judul', 'style' => 'title']],
 *     ['Nama', 'Total'],
 *     ['Kopi', ['v' => 15000, 'style' => 'money']],
 * ], [30, 18]);
 * $x->output();   // isi file xlsx dalam bentuk string
 *
 * style yang tersedia: header, title, bold, money, moneybold, int
 */
class XlsxWriter
{
    private $sheets = [];

    private static $styleIds = [
        'header' => 1,
        'money' => 2,
        'bold' => 3,
        'title' => 4,
        'int' => 5,
        'moneybold' => 6,
    ];

    // $rows = array baris, tiap baris array sel. Sel boleh angka / teks / ['v' => ..., 'style' => ...]
    public function addSheet($name, $rows, $widths = [])
    {
        // nama sheet maksimal 31 karakter dan tidak boleh ada karakter khusus
        $name = substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $name), 0, 31);

        $this->sheets[] = ['name' => $name, 'rows' => $rows, 'widths' => $widths];

        return $this;
    }

    public function output()
    {
        $file = tempnam(sys_get_temp_dir(), 'xlsx');

        $zip = new ZipArchive();
        $zip->open($file, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());

        foreach ($this->sheets as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->sheetXml($sheet));
        }

        $zip->close();

        $content = file_get_contents($file);
        unlink($file);

        return $content;
    }

    private function head()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    }

    private function esc($text)
    {
        return htmlspecialchars((string) $text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypes()
    {
        $xml = $this->head() . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>';
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        foreach ($this->sheets as $i => $sheet) {
            $xml .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return $xml . '</Types>';
    }

    private function rootRels()
    {
        return $this->head() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook()
    {
        $xml = $this->head() . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        foreach ($this->sheets as $i => $sheet) {
            $xml .= '<sheet name="' . $this->esc($sheet['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return $xml . '</sheets></workbook>';
    }

    private function workbookRels()
    {
        $xml = $this->head() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->sheets as $i => $sheet) {
            $xml .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }
        $xml .= '<Relationship Id="rId' . (count($this->sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return $xml . '</Relationships>';
    }

    private function styles()
    {
        // urutan xf harus sama dengan $styleIds
        return $this->head() . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;Rp &quot;#,##0"/></numFmts>'
            . '<fonts count="4">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF172033"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="7">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="3" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="164" fontId="1" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    // nomor kolom (0,1,2...) jadi huruf (A,B,...,Z,AA)
    private function columnName($index)
    {
        $name = '';
        $index++;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $name = chr(65 + $mod) . $name;
            $index = intdiv($index - 1, 26);
        }

        return $name;
    }

    private function sheetXml($sheet)
    {
        $xml = $this->head() . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        if (count($sheet['widths']) > 0) {
            $xml .= '<cols>';
            foreach ($sheet['widths'] as $i => $w) {
                $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';

        foreach ($sheet['rows'] as $r => $row) {
            $rowNumber = $r + 1;
            $xml .= '<row r="' . $rowNumber . '">';

            foreach ($row as $c => $cell) {
                $value = $cell;
                $style = null;

                if (is_array($cell)) {
                    $value = $cell['v'] ?? '';
                    $style = $cell['style'] ?? null;
                }

                if ($value === null || $value === '') {
                    // sel kosong tetap ditulis kalau punya style (misalnya header)
                    if ($style) {
                        $xml .= '<c r="' . $this->columnName($c) . $rowNumber . '" s="' . self::$styleIds[$style] . '"/>';
                    }
                    continue;
                }

                $ref = $this->columnName($c) . $rowNumber;
                $s = $style ? ' s="' . self::$styleIds[$style] . '"' : '';

                if (is_int($value) || is_float($value)) {
                    $xml .= '<c r="' . $ref . '"' . $s . '><v>' . $value . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '"' . $s . ' t="inlineStr"><is><t xml:space="preserve">' . $this->esc($value) . '</t></is></c>';
                }
            }

            $xml .= '</row>';
        }

        return $xml . '</sheetData></worksheet>';
    }
}
