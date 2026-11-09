<?php

namespace App\Support;

/**
 * Pembuat PDF sederhana untuk laporan (A4, huruf Helvetica bawaan PDF).
 * Tanpa library tambahan: teks, garis, kotak, dan tabel yang otomatis pindah halaman.
 */
class SimplePdf
{
    private $pageW = 595.28;
    private $pageH = 841.89;
    private $margin = 40;

    private $pages = [];
    private $content = '';
    private $title;

    // posisi vertikal sekarang, dihitung dari atas halaman
    public $y;

    // warna (RGB 0-1)
    private $ink = [0.09, 0.13, 0.20];
    private $yellow = [1, 0.82, 0.25];
    private $grey = [0.45, 0.47, 0.50];
    private $stripe = [0.94, 0.95, 0.93];

    // lebar huruf Helvetica per 1000 unit, karakter ASCII 32 sampai 126
    private static $widthRegular = [278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584];
    private static $widthBold = [278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,611,611,389,556,333,611,556,778,556,556,500,389,280,389,584];

    public function __construct($title)
    {
        $this->title = $title;
        $this->newPage();
    }

    public function newPage()
    {
        if ($this->content !== '') {
            $this->pages[] = $this->content;
        }

        $this->content = '';
        $this->y = $this->margin;
    }

    // ---------- ukuran teks ----------

    public function textWidth($text, $size, $bold = false)
    {
        $table = $bold ? self::$widthBold : self::$widthRegular;
        $total = 0;
        $length = strlen($this->encode($text));
        $encoded = $this->encode($text);

        for ($i = 0; $i < $length; $i++) {
            $code = ord($encoded[$i]);
            $total += ($code >= 32 && $code <= 126) ? $table[$code - 32] : 556;
        }

        return $total * $size / 1000;
    }

    // potong teks yang terlalu panjang supaya muat di kolom
    public function fit($text, $maxWidth, $size, $bold = false)
    {
        $text = (string) $text;

        if ($this->textWidth($text, $size, $bold) <= $maxWidth) {
            return $text;
        }

        while ($text !== '' && $this->textWidth($text . '..', $size, $bold) > $maxWidth) {
            $text = mb_substr($text, 0, -1);
        }

        return $text . '..';
    }

    private function encode($text)
    {
        $text = preg_replace('/[\x00-\x1F]/u', ' ', (string) $text);
        $converted = @mb_convert_encoding($text, 'Windows-1252', 'UTF-8');

        return $converted === false ? '?' : $converted;
    }

    private function escape($text)
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->encode($text));
    }

    private function num($n)
    {
        return number_format($n, 2, '.', '');
    }

    private function color($rgb, $stroke = false)
    {
        return $this->num($rgb[0]) . ' ' . $this->num($rgb[1]) . ' ' . $this->num($rgb[2]) . ($stroke ? ' RG ' : ' rg ');
    }

    // ---------- gambar dasar ----------

    // $align: left / right / center. $x untuk right = batas kanan, center = titik tengah
    public function text($x, $y, $text, $size = 10, $bold = false, $rgb = [0, 0, 0], $align = 'left')
    {
        $width = $this->textWidth($text, $size, $bold);

        if ($align == 'right') {
            $x -= $width;
        } elseif ($align == 'center') {
            $x -= $width / 2;
        }

        $this->content .= 'BT ' . $this->color($rgb) . '/' . ($bold ? 'F2' : 'F1') . ' ' . $size . ' Tf '
            . $this->num($x) . ' ' . $this->num($this->pageH - $y) . ' Td (' . $this->escape($text) . ') Tj ET' . "\n";
    }

    public function rect($x, $y, $w, $h, $rgb)
    {
        $this->content .= $this->color($rgb) . $this->num($x) . ' ' . $this->num($this->pageH - $y - $h) . ' '
            . $this->num($w) . ' ' . $this->num($h) . " re f\n";
    }

    public function line($x1, $y, $x2, $rgb = [0.8, 0.8, 0.8], $width = 0.5)
    {
        $this->content .= $this->color($rgb, true) . $width . ' w ' . $this->num($x1) . ' ' . $this->num($this->pageH - $y)
            . ' m ' . $this->num($x2) . ' ' . $this->num($this->pageH - $y) . " l S\n";
    }

    // ---------- bagian laporan ----------

    public function contentWidth()
    {
        return $this->pageW - $this->margin * 2;
    }

    // pindah halaman kalau sisa ruang tidak cukup
    public function ensureSpace($height)
    {
        if ($this->y + $height > $this->pageH - $this->margin - 20) {
            $this->newPage();
            return true;
        }

        return false;
    }

    // kepala laporan di halaman pertama
    public function brandHeader($title, $lines)
    {
        $this->rect(0, 0, $this->pageW, 92, $this->ink);
        $this->rect($this->margin, 26, 8, 40, $this->yellow);
        $this->text($this->margin + 20, 44, 'POS UMKM', 11, true, $this->yellow);
        $this->text($this->margin + 20, 64, $title, 17, true, [1, 1, 1]);

        $y = 40;
        foreach ($lines as $line) {
            $this->text($this->pageW - $this->margin, $y, $line, 9, false, [0.85, 0.87, 0.9], 'right');
            $y += 13;
        }

        $this->y = 118;
    }

    public function heading($text)
    {
        $this->ensureSpace(60);
        $this->y += 8;
        $this->text($this->margin, $this->y, $text, 12, true, $this->ink);
        $this->y += 6;
        $this->line($this->margin, $this->y, $this->pageW - $this->margin, $this->ink, 1);
        $this->y += 16;
    }

    // daftar "label : nilai" dua kolom
    public function keyValue($rows)
    {
        foreach ($rows as $row) {
            $this->ensureSpace(18);
            $this->text($this->margin, $this->y, $row[0], 10, false, $this->grey);
            $this->text($this->margin + 200, $this->y, $row[1], 10, !empty($row[2]));
            $this->y += 16;
        }
        $this->y += 4;
    }

    /**
     * Tabel.
     * $widths : lebar relatif tiap kolom, mis. [3, 1, 2]
     * $aligns : left / right per kolom
     * $boldLast : baris terakhir ditebalkan (baris total)
     */
    public function table($headers, $rows, $widths, $aligns = [], $boldLast = false)
    {
        $sum = array_sum($widths);
        $cols = [];
        $x = $this->margin;
        foreach ($widths as $w) {
            $width = $this->contentWidth() * $w / $sum;
            $cols[] = ['x' => $x, 'w' => $width];
            $x += $width;
        }

        $this->tableHeader($headers, $cols, $aligns);

        $lastIndex = count($rows) - 1;

        foreach ($rows as $i => $row) {
            if ($this->ensureSpace(18)) {
                $this->tableHeader($headers, $cols, $aligns);
            }

            $bold = $boldLast && $i == $lastIndex;

            if ($i % 2 == 1 && !$bold) {
                $this->rect($this->margin, $this->y - 11, $this->contentWidth(), 16, $this->stripe);
            }
            if ($bold) {
                $this->line($this->margin, $this->y - 11, $this->pageW - $this->margin, $this->ink, 0.8);
            }

            foreach ($row as $c => $value) {
                $this->cell($cols[$c], $value, $aligns[$c] ?? 'left', $bold);
            }

            $this->y += 16;
        }

        $this->y += 6;
    }

    private function tableHeader($headers, $cols, $aligns)
    {
        $this->rect($this->margin, $this->y - 12, $this->contentWidth(), 18, $this->ink);

        foreach ($headers as $c => $label) {
            $this->cell($cols[$c], $label, $aligns[$c] ?? 'left', true, [1, 1, 1]);
        }

        $this->y += 18;
    }

    private function cell($col, $value, $align, $bold, $rgb = [0, 0, 0])
    {
        $pad = 5;
        $text = $this->fit($value, $col['w'] - $pad * 2, 9, $bold);

        if ($align == 'right') {
            $this->text($col['x'] + $col['w'] - $pad, $this->y, $text, 9, $bold, $rgb, 'right');
        } else {
            $this->text($col['x'] + $pad, $this->y, $text, 9, $bold, $rgb);
        }
    }

    // ---------- hasil akhir ----------

    public function output()
    {
        $pages = $this->pages;
        if ($this->content !== '') {
            $pages[] = $this->content;
        }

        // simpan konten halaman aktif lalu tambahkan footer di semua halaman
        $total = count($pages);
        foreach ($pages as $i => $stream) {
            $this->content = '';
            $this->line($this->margin, $this->pageH - 34, $this->pageW - $this->margin, [0.8, 0.8, 0.8], 0.5);
            $this->text($this->margin, $this->pageH - 22, 'POS UMKM - ' . $this->title, 8, false, $this->grey);
            $this->text($this->pageW - $this->margin, $this->pageH - 22, 'Halaman ' . ($i + 1) . ' dari ' . $total, 8, false, $this->grey, 'right');
            $pages[$i] = $stream . $this->content;
        }

        // susun objek PDF
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = [];
        for ($i = 0; $i < $total; $i++) {
            $kids[] = (5 + $i * 2) . ' 0 R';
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $total . ' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        foreach ($pages as $i => $stream) {
            $pageId = 5 + $i * 2;
            $contentId = $pageId + 1;

            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->num($this->pageW) . ' ' . $this->num($this->pageH) . '] '
                . '/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
            $objects[$contentId] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $count = count($objects);

        for ($id = 1; $id <= $count; $id++) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $objects[$id] . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . ($count + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= $count; $id++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$id]) . "\n";
        }
        $pdf .= "trailer\n<< /Size " . ($count + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }
}
