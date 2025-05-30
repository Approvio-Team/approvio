<?php

namespace Approvio\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Basis-Testklasse für alle Approvio-Tests
 */
class TestCase extends BaseTestCase
{
    /**
     * Gemeinsame Setup-Funktionalität für alle Tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Hier können gemeinsame Setup-Schritte für alle Tests erfolgen
    }

    /**
     * Gemeinsame Teardown-Funktionalität für alle Tests
     */
    protected function tearDown(): void
    {
        // Hier können gemeinsame Cleanup-Schritte für alle Tests erfolgen
        parent::tearDown();
    }

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
