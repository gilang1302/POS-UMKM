<?php

namespace App\Support;

/**
 * Pembuat QR code sederhana (mode byte, koreksi error level M, versi 1-10).
 * Cukup untuk teks pendek seperti kode pembayaran. Hasilnya berupa SVG.
 *
 * pemakaian: echo QrCode::svg('teks yang mau dijadikan QR');
 */
class QrCode
{
    // tabel per versi untuk level M: [jumlah blok grup 1, data per blok grup 1,
    // jumlah blok grup 2, data per blok grup 2, kode koreksi per blok]
    private static $blocks = [
        1 => [1, 16, 0, 0, 10],
        2 => [1, 28, 0, 0, 16],
        3 => [1, 44, 0, 0, 26],
        4 => [2, 32, 0, 0, 18],
        5 => [2, 43, 0, 0, 24],
        6 => [4, 27, 0, 0, 16],
        7 => [4, 31, 0, 0, 18],
        8 => [2, 38, 2, 39, 22],
        9 => [3, 36, 2, 37, 22],
        10 => [4, 43, 1, 44, 26],
    ];

    // posisi pola penyelaras per versi
    private static $alignment = [
        1 => [],
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
        7 => [6, 22, 38],
        8 => [6, 24, 42],
        9 => [6, 26, 46],
        10 => [6, 28, 50],
    ];

    private $size;
    private $modules;   // true / false / null (null = belum diisi)
    private $reserved;  // true kalau modul termasuk pola tetap

    private static $exp = [];
    private static $log = [];

    // hasilkan matriks QR (array 2 dimensi berisi true/false)
    public static function matrix($text)
    {
        $qr = new self();
        return $qr->build($text);
    }

    public static function svg($text, $pixel = 6, $quiet = 4)
    {
        $matrix = self::matrix($text);
        $n = count($matrix);
        $total = ($n + $quiet * 2) * $pixel;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $total . '" height="' . $total . '" viewBox="0 0 ' . ($n + $quiet * 2) . ' ' . ($n + $quiet * 2) . '" shape-rendering="crispEdges">';
        $svg .= '<rect width="100%" height="100%" fill="#fff"/>';
        $svg .= '<path fill="#000" d="';

        // gabungkan modul gelap yang berurutan dalam satu baris jadi satu kotak
        for ($y = 0; $y < $n; $y++) {
            $x = 0;
            while ($x < $n) {
                if ($matrix[$y][$x]) {
                    $start = $x;
                    while ($x < $n && $matrix[$y][$x]) {
                        $x++;
                    }
                    $svg .= 'M' . ($start + $quiet) . ' ' . ($y + $quiet) . 'h' . ($x - $start) . 'v1h-' . ($x - $start) . 'z';
                } else {
                    $x++;
                }
            }
        }

        return $svg . '"/></svg>';
    }

    private function build($text)
    {
        $bytes = array_values(unpack('C*', $text));
        $version = $this->pickVersion(count($bytes));

        $data = $this->makeDataCodewords($bytes, $version);
        $final = $this->interleave($data, $version);

        $this->size = 17 + $version * 4;
        $this->modules = array_fill(0, $this->size, array_fill(0, $this->size, null));
        $this->reserved = array_fill(0, $this->size, array_fill(0, $this->size, false));

        $this->drawFunctionPatterns($version);

        // coba 8 mask, pakai yang nilai penaltinya paling kecil
        $bestMask = 0;
        $bestScore = null;
        $bestModules = null;

        for ($mask = 0; $mask < 8; $mask++) {
            $saved = $this->modules;
            $this->placeData($final, $mask);
            $this->drawFormatInfo($mask);
            $score = $this->penalty();

            if ($bestScore === null || $score < $bestScore) {
                $bestScore = $score;
                $bestMask = $mask;
                $bestModules = $this->modules;
            }
            $this->modules = $saved;
        }

        $this->modules = $bestModules;

        $result = [];
        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $result[$y][$x] = $this->modules[$y][$x] === true;
            }
        }

        return $result;
    }

    private function pickVersion($length)
    {
        foreach (self::$blocks as $version => $b) {
            $capacity = $b[0] * $b[1] + $b[2] * $b[3];
            $countBits = $version >= 10 ? 16 : 8;
            // 4 bit mode + bit jumlah karakter + 8 bit per byte
            $needBits = 4 + $countBits + $length * 8;

            if ($needBits <= $capacity * 8) {
                return $version;
            }
        }

        throw new \Exception('Teks terlalu panjang untuk QR');
    }

    private function makeDataCodewords($bytes, $version)
    {
        $b = self::$blocks[$version];
        $capacity = $b[0] * $b[1] + $b[2] * $b[3];

        $bits = '0100';
        $bits .= str_pad(decbin(count($bytes)), $version >= 10 ? 16 : 8, '0', STR_PAD_LEFT);
        foreach ($bytes as $byte) {
            $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        // terminator, lalu genapkan ke 8 bit
        $bits .= str_repeat('0', min(4, $capacity * 8 - strlen($bits)));
        if (strlen($bits) % 8 != 0) {
            $bits .= str_repeat('0', 8 - strlen($bits) % 8);
        }

        $words = [];
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $words[] = bindec(substr($bits, $i, 8));
        }

        // isi sisa dengan byte pengganjal
        $pad = [0xEC, 0x11];
        $i = 0;
        while (count($words) < $capacity) {
            $words[] = $pad[$i % 2];
            $i++;
        }

        return $words;
    }

    private function interleave($data, $version)
    {
        $b = self::$blocks[$version];
        $ecLen = $b[4];

        // bagi data ke beberapa blok
        $dataBlocks = [];
        $pos = 0;
        for ($i = 0; $i < $b[0]; $i++) {
            $dataBlocks[] = array_slice($data, $pos, $b[1]);
            $pos += $b[1];
        }
        for ($i = 0; $i < $b[2]; $i++) {
            $dataBlocks[] = array_slice($data, $pos, $b[3]);
            $pos += $b[3];
        }

        $ecBlocks = [];
        foreach ($dataBlocks as $block) {
            $ecBlocks[] = $this->reedSolomon($block, $ecLen);
        }

        $result = [];
        $maxData = max($b[1], $b[3]);
        for ($i = 0; $i < $maxData; $i++) {
            foreach ($dataBlocks as $block) {
                if (isset($block[$i])) {
                    $result[] = $block[$i];
                }
            }
        }
        for ($i = 0; $i < $ecLen; $i++) {
            foreach ($ecBlocks as $block) {
                $result[] = $block[$i];
            }
        }

        return $result;
    }

    // ---------- aritmatika Galois field 256 + Reed-Solomon ----------

    private function initGalois()
    {
        if (count(self::$exp) > 0) {
            return;
        }

        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $x;
            self::$log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }
    }

    private function gfMul($a, $b)
    {
        if ($a == 0 || $b == 0) {
            return 0;
        }

        return self::$exp[self::$log[$a] + self::$log[$b]];
    }

    private function reedSolomon($data, $ecLen)
    {
        $this->initGalois();

        // polinomial pembangkit
        $gen = [1];
        for ($i = 0; $i < $ecLen; $i++) {
            $next = array_fill(0, count($gen) + 1, 0);
            foreach ($gen as $j => $coef) {
                $next[$j] ^= $coef;
                $next[$j + 1] ^= $this->gfMul($coef, self::$exp[$i]);
            }
            $gen = $next;
        }

        $remainder = array_fill(0, $ecLen, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($remainder);
            $remainder[] = 0;
            for ($i = 0; $i < $ecLen; $i++) {
                $remainder[$i] ^= $this->gfMul($gen[$i + 1], $factor);
            }
        }

        return $remainder;
    }

    // ---------- pola tetap ----------

    private function setFunction($x, $y, $dark)
    {
        $this->modules[$y][$x] = $dark;
        $this->reserved[$y][$x] = true;
    }

    private function drawFunctionPatterns($version)
    {
        $n = $this->size;

        // pola pencari di tiga pojok + separator
        $this->drawFinder(3, 3);
        $this->drawFinder($n - 4, 3);
        $this->drawFinder(3, $n - 4);

        // pola penyelaras
        $pos = self::$alignment[$version];
        $last = count($pos) - 1;
        foreach ($pos as $i => $cy) {
            foreach ($pos as $j => $cx) {
                // lewati yang menimpa pola pencari
                if (($i == 0 && $j == 0) || ($i == 0 && $j == $last) || ($i == $last && $j == 0)) {
                    continue;
                }
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        $dark = max(abs($dx), abs($dy)) != 1;
                        $this->setFunction($cx + $dx, $cy + $dy, $dark);
                    }
                }
            }
        }

        // pola waktu (garis putus-putus)
        for ($i = 8; $i < $n - 8; $i++) {
            $this->setFunction($i, 6, $i % 2 == 0);
            $this->setFunction(6, $i, $i % 2 == 0);
        }

        // area format info dikosongkan dulu
        for ($i = 0; $i < 9; $i++) {
            $this->reserved[8][$i] = true;
            $this->reserved[$i][8] = true;
        }
        for ($i = 0; $i < 8; $i++) {
            $this->reserved[8][$n - 1 - $i] = true;
            $this->reserved[$n - 1 - $i][8] = true;
        }

        // satu modul gelap tetap
        $this->setFunction(8, $n - 8, true);

        if ($version >= 7) {
            $this->drawVersionInfo($version);
        }
    }

    private function drawFinder($cx, $cy)
    {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $x = $cx + $dx;
                $y = $cy + $dy;
                if ($x < 0 || $y < 0 || $x >= $this->size || $y >= $this->size) {
                    continue;
                }
                $dist = max(abs($dx), abs($dy));
                $this->setFunction($x, $y, $dist != 2 && $dist != 4);
            }
        }
    }

    private function drawVersionInfo($version)
    {
        $rem = $version;
        for ($i = 0; $i < 12; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 11) * 0x1F25);
        }
        $bits = ($version << 12) | $rem;

        for ($i = 0; $i < 18; $i++) {
            $dark = (($bits >> $i) & 1) == 1;
            $a = $this->size - 11 + $i % 3;
            $b = intdiv($i, 3);
            $this->setFunction($a, $b, $dark);
            $this->setFunction($b, $a, $dark);
        }
    }

    // level M = bit 00
    private function drawFormatInfo($mask)
    {
        $data = (0 << 3) | $mask;
        $rem = $data;
        for ($i = 0; $i < 10; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 9) * 0x537);
        }
        $bits = (($data << 10) | $rem) ^ 0x5412;

        $n = $this->size;

        for ($i = 0; $i <= 5; $i++) {
            $this->modules[$i][8] = (($bits >> $i) & 1) == 1;
        }
        $this->modules[7][8] = (($bits >> 6) & 1) == 1;
        $this->modules[8][8] = (($bits >> 7) & 1) == 1;
        $this->modules[8][7] = (($bits >> 8) & 1) == 1;
        for ($i = 9; $i < 15; $i++) {
            $this->modules[8][14 - $i] = (($bits >> $i) & 1) == 1;
        }

        for ($i = 0; $i < 8; $i++) {
            $this->modules[8][$n - 1 - $i] = (($bits >> $i) & 1) == 1;
        }
        for ($i = 8; $i < 15; $i++) {
            $this->modules[$n - 15 + $i][8] = (($bits >> $i) & 1) == 1;
        }
        $this->modules[$n - 8][8] = true;
    }

    // ---------- penempatan data + mask ----------

    private function placeData($codewords, $mask)
    {
        $n = $this->size;

        $bits = [];
        foreach ($codewords as $word) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($word >> $i) & 1;
            }
        }

        $index = 0;
        $upward = true;

        // baca dua kolom sekaligus dari kanan ke kiri, kolom 6 dilewati
        for ($right = $n - 1; $right >= 1; $right -= 2) {
            if ($right == 6) {
                $right = 5;
            }

            for ($step = 0; $step < $n; $step++) {
                $y = $upward ? $n - 1 - $step : $step;

                for ($k = 0; $k < 2; $k++) {
                    $x = $right - $k;

                    if ($this->reserved[$y][$x]) {
                        continue;
                    }

                    $dark = $index < count($bits) ? $bits[$index] == 1 : false;
                    $index++;

                    if ($this->maskApplies($mask, $x, $y)) {
                        $dark = !$dark;
                    }

                    $this->modules[$y][$x] = $dark;
                }
            }

            $upward = !$upward;
        }
    }

    private function maskApplies($mask, $x, $y)
    {
        switch ($mask) {
            case 0: return ($x + $y) % 2 == 0;
            case 1: return $y % 2 == 0;
            case 2: return $x % 3 == 0;
            case 3: return ($x + $y) % 3 == 0;
            case 4: return (intdiv($x, 3) + intdiv($y, 2)) % 2 == 0;
            case 5: return ($x * $y) % 2 + ($x * $y) % 3 == 0;
            case 6: return (($x * $y) % 2 + ($x * $y) % 3) % 2 == 0;
            default: return (($x + $y) % 2 + ($x * $y) % 3) % 2 == 0;
        }
    }

    // ---------- nilai penalti untuk memilih mask ----------

    private function penalty()
    {
        $n = $this->size;
        $m = $this->modules;
        $score = 0;

        // aturan 1: deretan 5+ modul sewarna (baris dan kolom)
        for ($pass = 0; $pass < 2; $pass++) {
            for ($a = 0; $a < $n; $a++) {
                $run = 1;
                for ($b = 1; $b < $n; $b++) {
                    $cur = $pass == 0 ? $m[$a][$b] : $m[$b][$a];
                    $prev = $pass == 0 ? $m[$a][$b - 1] : $m[$b - 1][$a];

                    if ($cur === $prev) {
                        $run++;
                    } else {
                        if ($run >= 5) {
                            $score += $run - 2;
                        }
                        $run = 1;
                    }
                }
                if ($run >= 5) {
                    $score += $run - 2;
                }
            }
        }

        // aturan 2: blok 2x2 sewarna
        for ($y = 0; $y < $n - 1; $y++) {
            for ($x = 0; $x < $n - 1; $x++) {
                if ($m[$y][$x] === $m[$y][$x + 1] && $m[$y][$x] === $m[$y + 1][$x] && $m[$y][$x] === $m[$y + 1][$x + 1]) {
                    $score += 3;
                }
            }
        }

        // aturan 3: pola mirip pola pencari (1011101 dengan 4 modul terang di sampingnya)
        $pattern = [true, false, true, true, true, false, true];
        for ($pass = 0; $pass < 2; $pass++) {
            for ($a = 0; $a < $n; $a++) {
                for ($b = 0; $b <= $n - 7; $b++) {
                    $match = true;
                    for ($k = 0; $k < 7; $k++) {
                        $v = $pass == 0 ? $m[$a][$b + $k] : $m[$b + $k][$a];
                        if (($v === true) !== $pattern[$k]) {
                            $match = false;
                            break;
                        }
                    }
                    if (!$match) {
                        continue;
                    }

                    $before = true;
                    for ($k = 1; $k <= 4; $k++) {
                        $i = $b - $k;
                        $v = $i < 0 ? false : ($pass == 0 ? $m[$a][$i] : $m[$i][$a]);
                        if ($v === true) {
                            $before = false;
                        }
                    }
                    $after = true;
                    for ($k = 7; $k <= 10; $k++) {
                        $i = $b + $k;
                        $v = $i >= $n ? false : ($pass == 0 ? $m[$a][$i] : $m[$i][$a]);
                        if ($v === true) {
                            $after = false;
                        }
                    }
                    if ($before || $after) {
                        $score += 40;
                    }
                }
            }
        }

        // aturan 4: keseimbangan modul gelap dan terang
        $dark = 0;
        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($m[$y][$x] === true) {
                    $dark++;
                }
            }
        }
        $percent = $dark * 100 / ($n * $n);
        $score += (int) (abs($percent - 50) / 5) * 10;

        return $score;
    }
}
