<?php
class BrowserPdf
{
    private string $projectRoot;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?: dirname(__DIR__, 2);
    }

    public function renderHtml(string $html): string
    {
        if (!function_exists('proc_open')) {
            throw new RuntimeException('La fonction PHP proc_open() est désactivée.');
        }

        $browser = $this->findBrowser();
        if ($browser === null) {
            throw new RuntimeException('Google Chrome ou Microsoft Edge est introuvable.');
        }

        $base = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cv_pdf_' . bin2hex(random_bytes(8));
        if (!@mkdir($base, 0700, true) && !is_dir($base)) {
            throw new RuntimeException('Impossible de créer le dossier temporaire du PDF.');
        }

        $htmlPath = $base . DIRECTORY_SEPARATOR . 'cv.html';
        $pdfPath = $base . DIRECTORY_SEPARATOR . 'cv.pdf';
        $profilePath = $base . DIRECTORY_SEPARATOR . 'profile';
        @mkdir($profilePath, 0700, true);

        if (@file_put_contents($htmlPath, $html) === false) {
            $this->removeDir($base);
            throw new RuntimeException('Impossible de préparer le CV pour le PDF.');
        }

        $args = [
            $browser,
            '--headless=new',
            '--disable-gpu',
            '--disable-extensions',
            '--disable-dev-shm-usage',
            '--disable-background-networking',
            '--disable-sync',
            '--no-first-run',
            '--no-default-browser-check',
            '--hide-scrollbars',
            '--window-size=900,1273',
            '--force-device-scale-factor=1',
            '--no-pdf-header-footer',
            '--print-to-pdf-no-header',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=1200',
            '--user-data-dir=' . $profilePath,
            '--print-to-pdf=' . $pdfPath,
            $this->fileUrl($htmlPath),
        ];

        if (DIRECTORY_SEPARATOR === '/') {
            array_splice($args, 2, 0, ['--no-sandbox']);
        }

        $command = implode(' ', array_map('escapeshellarg', $args));
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptors, $pipes, $base);
        if (!is_resource($process)) {
            $this->removeDir($base);
            throw new RuntimeException('Impossible de lancer Chrome/Edge pour créer le PDF.');
        }

        @fclose($pipes[0]);
        @stream_set_blocking($pipes[1], false);
        @stream_set_blocking($pipes[2], false);

        $deadline = microtime(true) + 18.0;
        $lastSize = -1;
        $stableTicks = 0;
        $debug = '';

        while (microtime(true) < $deadline) {
            $debug .= (string) @stream_get_contents($pipes[1]);
            $debug .= (string) @stream_get_contents($pipes[2]);

            clearstatcache(true, $pdfPath);
            if (is_file($pdfPath)) {
                $size = (int) @filesize($pdfPath);
                if ($size > 1000 && $size === $lastSize) {
                    $stableTicks++;
                } else {
                    $stableTicks = 0;
                }
                $lastSize = $size;

                // PDF has been fully written. No need to wait for lingering browser helpers.
                if ($stableTicks >= 3) {
                    break;
                }
            }

            $status = @proc_get_status($process);
            if (is_array($status) && !$status['running'] && is_file($pdfPath)) {
                break;
            }
            usleep(120000);
        }

        $debug .= (string) @stream_get_contents($pipes[1]);
        $debug .= (string) @stream_get_contents($pipes[2]);
        @fclose($pipes[1]);
        @fclose($pipes[2]);

        $status = @proc_get_status($process);
        if (is_array($status) && $status['running']) {
            @proc_terminate($process);
            usleep(150000);
        }
        @proc_close($process);

        $bytes = is_file($pdfPath) ? @file_get_contents($pdfPath) : false;
        $this->removeDir($base);

        if ($bytes === false || strlen($bytes) < 1000 || strncmp($bytes, '%PDF-', 5) !== 0) {
            $debug = trim(preg_replace('/\s+/', ' ', $debug));
            if (strlen($debug) > 350) $debug = substr($debug, -350);
            throw new RuntimeException('Le navigateur n’a pas pu créer le PDF.' . ($debug !== '' ? ' ' . $debug : ''));
        }

        return $bytes;
    }

    private function findBrowser(): ?string
    {
        $env = getenv('CV_BROWSER_BIN');
        if ($env && is_file($env)) return $env;

        $candidates = [];
        if (PHP_OS_FAMILY === 'Windows') {
            foreach (array_filter([
                getenv('PROGRAMFILES'),
                getenv('PROGRAMFILES(X86)'),
                getenv('LOCALAPPDATA'),
            ]) as $base) {
                $candidates[] = $base . '\\Google\\Chrome\\Application\\chrome.exe';
                $candidates[] = $base . '\\Microsoft\\Edge\\Application\\msedge.exe';
                $candidates[] = $base . '\\Chromium\\Application\\chrome.exe';
            }
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $candidates = [
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
                '/Applications/Chromium.app/Contents/MacOS/Chromium',
            ];
        } else {
            // Fixed common paths first so the class does not depend on shell_exec().
            $candidates = [
                '/usr/bin/google-chrome-stable', '/usr/bin/google-chrome',
                '/usr/bin/chromium', '/usr/bin/chromium-browser',
                '/usr/bin/microsoft-edge-stable', '/usr/bin/microsoft-edge',
            ];
        }

        foreach ($candidates as $candidate) {
            if ($candidate && is_file($candidate)) return $candidate;
        }
        return null;
    }

    private function fileUrl(string $path): string
    {
        $real = str_replace('\\', '/', realpath($path) ?: $path);
        $encoded = rawurlencode($real);
        $encoded = str_ireplace(['%2F', '%3A'], ['/', ':'], $encoded);
        return preg_match('/^[A-Za-z]:\//', $real) ? 'file:///' . $encoded : 'file://' . $encoded;
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = @scandir($dir);
        if ($items) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) $this->removeDir($path); else @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
