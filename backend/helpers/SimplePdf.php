<?php
class SimplePdf
{
    public const A4_W = 595.28;
    public const A4_H = 841.89;

    private array $pages = [];
    private int $pageIndex = -1;
    private array $images = [];
    private array $imageMap = [];

    public function addPage(): void
    {
        $this->pages[] = '';
        $this->pageIndex = count($this->pages) - 1;
    }

    private function ensurePage(): void
    {
        if ($this->pageIndex < 0) {
            $this->addPage();
        }
    }

    private function add(string $s): void
    {
        $this->ensurePage();
        $this->pages[$this->pageIndex] .= $s;
    }

    public function pageWidth(): float { return self::A4_W; }
    public function pageHeight(): float { return self::A4_H; }

    public static function rgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return [0, 0, 0];
        }
        return [hexdec(substr($hex,0,2))/255, hexdec(substr($hex,2,2))/255, hexdec(substr($hex,4,2))/255];
    }

    private static function colorOp(string $hex, bool $stroke = false): string
    {
        [$r,$g,$b] = self::rgb($hex);
        return sprintf('%.4F %.4F %.4F %s', $r,$g,$b, $stroke ? 'RG' : 'rg');
    }

    public function fillRect(float $x, float $y, float $w, float $h, string $fill): void
    {
        $py = self::A4_H - $y - $h;
        $this->add("q\n" . self::colorOp($fill) . "\n" .
            sprintf('%.2F %.2F %.2F %.2F re f', $x,$py,$w,$h) . "\nQ\n");
    }

    public function strokeRect(float $x, float $y, float $w, float $h, string $stroke, float $lineWidth = 1): void
    {
        $py = self::A4_H - $y - $h;
        $this->add("q\n" . self::colorOp($stroke, true) . "\n" .
            sprintf('%.2F w %.2F %.2F %.2F %.2F re S', $lineWidth,$x,$py,$w,$h) . "\nQ\n");
    }

    public function line(float $x1, float $y1, float $x2, float $y2, string $stroke = '#000000', float $lineWidth = 1): void
    {
        $py1 = self::A4_H - $y1;
        $py2 = self::A4_H - $y2;
        $this->add("q\n" . self::colorOp($stroke, true) . "\n" .
            sprintf('%.2F w %.2F %.2F m %.2F %.2F l S', $lineWidth,$x1,$py1,$x2,$py2) . "\nQ\n");
    }

    public function circle(float $cx, float $cy, float $r, string $fill): void
    {
        $k = 0.5522847498;
        $py = self::A4_H - $cy;
        $c = $r * $k;
        $this->add("q\n" . self::colorOp($fill) . "\n" . sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c f',
            $cx+$r,$py,
            $cx+$r,$py+$c,$cx+$c,$py+$r,$cx,$py+$r,
            $cx-$c,$py+$r,$cx-$r,$py+$c,$cx-$r,$py,
            $cx-$r,$py-$c,$cx-$c,$py-$r,$cx,$py-$r,
            $cx+$c,$py-$r,$cx+$r,$py-$c,$cx+$r,$py
        ) . "\nQ\n");
    }

    private static function encodeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        } else {
            $text = preg_replace('/[^\x20-\x7E\n]/u', '?', $text) ?? $text;
        }
        return $text;
    }

    private static function escapePdfText(string $text): string
    {
        $text = self::encodeText($text);
        return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $text);
    }

    private static function fontKey(string $font): string
    {
        return match ($font) {
            'bold' => 'F2',
            'serif' => 'F3',
            'serif-bold' => 'F4',
            default => 'F1',
        };
    }

    public function text(float $x, float $y, string $text, float $size = 11, string $font = 'regular', string $color = '#000000'): void
    {
        if ($text === '') return;
        $baseline = self::A4_H - $y - $size;
        $escaped = self::escapePdfText($text);
        $fontKey = self::fontKey($font);
        $this->add("BT\n" . self::colorOp($color) . "\n" .
            sprintf('/%s %.2F Tf %.2F %.2F Td (%s) Tj', $fontKey,$size,$x,$baseline,$escaped) . "\nET\n");
    }

    public function textRight(float $rightX, float $y, string $text, float $size = 11, string $font = 'regular', string $color = '#000000'): void
    {
        $this->text($rightX - $this->textWidth($text,$size,$font), $y, $text, $size, $font, $color);
    }

    public function textCentered(float $centerX, float $y, string $text, float $size = 11, string $font = 'regular', string $color = '#000000'): void
    {
        $this->text($centerX - $this->textWidth($text,$size,$font)/2, $y, $text, $size, $font, $color);
    }

    public function textWidth(string $text, float $size, string $font = 'regular'): float
    {
        $encoded = self::encodeText($text);
        $factor = in_array($font, ['serif','serif-bold'], true) ? 0.48 : 0.50;
        if (in_array($font, ['bold','serif-bold'], true)) $factor += 0.015;
        return strlen($encoded) * $size * $factor;
    }

    public function wrapText(string $text, float $width, float $size, string $font = 'regular'): array
    {
        $paragraphs = preg_split('/\R/u', trim($text)) ?: [''];
        $lines = [];
        foreach ($paragraphs as $pIndex => $paragraph) {
            $words = preg_split('/\s+/u', trim($paragraph)) ?: [];
            $line = '';
            foreach ($words as $word) {
                if ($word === '') continue;
                $test = $line === '' ? $word : $line . ' ' . $word;
                if ($line !== '' && $this->textWidth($test, $size, $font) > $width) {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $line = $test;
                }
            }
            if ($line !== '') $lines[] = $line;
            if ($pIndex < count($paragraphs)-1) $lines[] = '';
        }
        return $lines;
    }

    public function paragraph(float $x, float $y, float $width, string $text, float $size = 10, string $font = 'regular', string $color = '#000000', float $lineHeight = 14, ?int $maxLines = null): float
    {
        $lines = $this->wrapText($text, $width, $size, $font);
        if ($maxLines !== null && count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $last = count($lines)-1;
            if ($last >= 0) {
                $lines[$last] = rtrim($lines[$last], ' .') . '...';
            }
        }
        foreach ($lines as $line) {
            if ($line !== '') $this->text($x, $y, $line, $size, $font, $color);
            $y += $lineHeight;
        }
        return $y;
    }

    public function image(string $path, float $x, float $y, float $w, float $h): bool
    {
        $real = realpath($path);
        if (!$real || !is_file($real)) return false;
        $info = @getimagesize($real);
        if (!$info) return false;
        $mime = $info['mime'] ?? '';
        $source = $real;
        $tmp = null;

        if ($mime !== 'image/jpeg') {
            if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
                return false;
            }
            $raw = @file_get_contents($real);
            $im = $raw !== false ? @imagecreatefromstring($raw) : false;
            if (!$im) return false;
            $tmp = tempnam(sys_get_temp_dir(), 'cvpdf_');
            @imagejpeg($im, $tmp, 92);
            imagedestroy($im);
            $source = $tmp;
            $info = @getimagesize($source);
            if (!$info) { @unlink($tmp); return false; }
        }

        $key = sha1_file($source) ?: md5($source);
        if (!isset($this->imageMap[$key])) {
            $bytes = @file_get_contents($source);
            if ($bytes === false) { if ($tmp) @unlink($tmp); return false; }
            $index = count($this->images);
            $this->images[] = [
                'name' => 'Im' . ($index + 1),
                'data' => $bytes,
                'width' => (int)$info[0],
                'height' => (int)$info[1],
            ];
            $this->imageMap[$key] = $index;
        }
        $img = $this->images[$this->imageMap[$key]];
        if ($tmp) @unlink($tmp);

        $py = self::A4_H - $y - $h;
        $this->add("q\n" . sprintf('%.2F 0 0 %.2F %.2F %.2F cm /%s Do', $w,$h,$x,$py,$img['name']) . "\nQ\n");
        return true;
    }

    public function output(): string
    {
        $this->ensurePage();
        $objects = [];
        $catalogObj = 1;
        $pagesObj = 2;
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";
        $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Times-Roman /Encoding /WinAnsiEncoding >>";
        $objects[6] = "<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold /Encoding /WinAnsiEncoding >>";

        $next = 7;
        $imageObjNums = [];
        foreach ($this->images as $i => $img) {
            $objNum = $next++;
            $imageObjNums[$img['name']] = $objNum;
            $objects[$objNum] = [
                'stream' => $img['data'],
                'dict' => "<< /Type /XObject /Subtype /Image /Width {$img['width']} /Height {$img['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($img['data']) . " >>",
            ];
        }

        $pageObjNums = [];
        $pagePairs = [];
        foreach ($this->pages as $content) {
            $pageObj = $next++;
            $contentObj = $next++;
            $pageObjNums[] = $pageObj;
            $pagePairs[] = [$pageObj,$contentObj,$content];
        }

        $kids = implode(' ', array_map(fn($n) => $n . ' 0 R', $pageObjNums));
        $objects[$catalogObj] = "<< /Type /Catalog /Pages {$pagesObj} 0 R >>";
        $objects[$pagesObj] = "<< /Type /Pages /Kids [{$kids}] /Count " . count($pageObjNums) . " >>";

        $xobj = '';
        foreach ($imageObjNums as $name => $num) $xobj .= "/{$name} {$num} 0 R ";
        $resources = "<< /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R /F4 6 0 R >>" . ($xobj !== '' ? " /XObject << {$xobj} >>" : '') . " >>";

        foreach ($pagePairs as [$pageObj,$contentObj,$content]) {
            $objects[$pageObj] = "<< /Type /Page /Parent {$pagesObj} 0 R /MediaBox [0 0 " . self::A4_W . ' ' . self::A4_H . "] /Resources {$resources} /Contents {$contentObj} 0 R >>";
            $objects[$contentObj] = [
                'stream' => $content,
                'dict' => "<< /Length " . strlen($content) . " >>",
            ];
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0 => 0];
        $maxObj = max(array_keys($objects));
        for ($i=1; $i <= $maxObj; $i++) {
            if (!array_key_exists($i, $objects)) continue;
            $offsets[$i] = strlen($pdf);
            $obj = $objects[$i];
            $pdf .= $i . " 0 obj\n";
            if (is_array($obj)) {
                $pdf .= $obj['dict'] . "\nstream\n" . $obj['stream'] . "\nendstream\n";
            } else {
                $pdf .= $obj . "\n";
            }
            $pdf .= "endobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxObj+1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i=1; $i <= $maxObj; $i++) {
            $off = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }
        $pdf .= "trailer\n<< /Size " . ($maxObj+1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        return $pdf;
    }
}
