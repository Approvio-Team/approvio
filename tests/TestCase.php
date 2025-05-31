<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Hilfsmethode zum Erstellen eines temporären Verzeichnisses für Tests
     */
    protected function createTempDirectory(): string
    {
        $tempDir = sys_get_temp_dir() . '/approvio_tests_' . uniqid();
        mkdir($tempDir, 0777, true);
        return $tempDir;
    }

    /**
     * Hilfsmethode zum Löschen eines Verzeichnisses und aller Inhalte
     */
    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
