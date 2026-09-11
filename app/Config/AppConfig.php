<?php
namespace App\Config;

class AppConfig {
    private static array $config = [];

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
        return $_ENV[$key] ?? self::$config[$key] ?? $default;
    }
}
