<?php

// ==========================================
// app/Helpers/system_helper.php - NOWY HELPER
// ==========================================

if (!function_exists('formatBytes')) {
    /**
     * Formatuje rozmiar plików w czytelnej formie
     */
    function formatBytes(int $size, int $precision = 2): string
    {
        if ($size === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        $index = floor($base);
        
        if ($index >= count($units)) {
            $index = count($units) - 1;
        }
        
        $value = $size / pow(1024, $index);
        
        return round($value, $precision) . ' ' . $units[$index];
    }
}

if (!function_exists('getSystemInfo')) {
    /**
     * Pobiera informacje o systemie
     */
    function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'ci_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'memory_usage' => formatBytes(memory_get_usage(true)),
            'memory_limit' => ini_get('memory_limit'),
            'timezone' => date_default_timezone_get(),
            'current_time' => date('H:i:s'),
            'current_date' => date('Y-m-d')
        ];
    }
}

if (!function_exists('getDatabaseStatus')) {
    /**
     * Sprawdza status bazy danych
     */
    function getDatabaseStatus(): array
    {
        try {
            $db = \Config\Database::connect();
            //if ($db->ping()) {
            if ($db->connID) {
                $version = $db->query("SELECT VERSION() as version")->getRow();
                $tableCount = $db->query("SHOW TABLES")->getNumRows();
                
                return [
                    'status' => 'online',
                    'version' => $version->version ?? 'N/A',
                    'tables' => $tableCount,
                    'badge_class' => 'success'
                ];
            } else {
                return [
                    'status' => 'offline',
                    'version' => 'N/A',
                    'tables' => 0,
                    'badge_class' => 'danger'
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Database status check failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'version' => 'N/A',
                'tables' => 0,
                'badge_class' => 'danger'
            ];
        }
    }
}

if (!function_exists('getApiStatus')) {
    /**
     * Sprawdza status konfiguracji API
     */
    function getApiStatus(): array
    {
        try {
            $settingsModel = model('SettingsModel');
            $apiKey = $settingsModel->getValue('lotto_api_key');
            
            if (!empty($apiKey)) {
                return [
                    'status' => 'configured',
                    'message' => 'Skonfigurowane',
                    'badge_class' => 'info'
                ];
            } else {
                return [
                    'status' => 'not_configured',
                    'message' => 'Brak klucza',
                    'badge_class' => 'warning'
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'API status check failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Błąd',
                'badge_class' => 'danger'
            ];
        }
    }
}