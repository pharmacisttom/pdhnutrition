<?php
namespace App\Config;

class AppConfig {
    private static array $config = [];

    public static function routeBase(): string {
        // Query routes work even when Apache rewrite and PATH_INFO are disabled.
        $base = (string) self::get('APP_URL', '/pdhnutrition');
        $path = parse_url($base, PHP_URL_PATH) ?: '';
        return rtrim($path, '/') . '/index.php?route=';
    }

    public static function load(): void {
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    $_ENV[$name] = $value;
                    self::$config[$name] = $value;
                }
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed {
        if (empty(self::$config)) {
            self::load();
        }

        // Dynamically resolve APP_URL matching defined constant or request host
        if ($key === 'APP_URL') {
            if (defined('APP_URL')) {
                return APP_URL;
            }
            if (!empty($_SERVER['HTTP_HOST'])) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                return $scheme . '://' . $_SERVER['HTTP_HOST'] . '/pdhnutrition';
            }
            return '/pdhnutrition';
        }

        return $_ENV[$key] ?? self::$config[$key] ?? $default;
    }
}
