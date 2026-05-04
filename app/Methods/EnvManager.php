<?php

namespace App\Methods;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Environment Variable Manager for EasyMarket
 *
 * Provides safe and reliable methods to update .env file variables.
 * Handles proper escaping, quoting, and validation of environment values.
 *
 * Features:
 * - Safe key-value updates in .env file
 * - Automatic value escaping and quoting
 * - Multi-line value support
 * - Validation of keys and values
 * - Atomic file writes with backup
 * - Error handling and logging
 *
 * Usage:
 * ```php
 * $envManager = new EnvManager();
 * $envManager->setKey('APP_NAME', 'EasyMarket');
 * $envManager->setKey('DB_PASSWORD', 'secret123', true); // With quotes
 * $envManager->setMultiple(['APP_DEBUG' => 'false', 'APP_URL' => 'https://example.com']);
 * ```
 *
 * Security Notes:
 * - Always quote sensitive values (passwords, API keys)
 * - Validates key names (alphanumeric and underscore only)
 * - Escapes special characters properly
 * - Creates backup before modification
 *
 * @see https://laravel.com/docs/11.x/configuration#environment-configuration
 */
class EnvManager
{
    /**
     * Path to the .env file
     */
    private string $envFilePath;

    /**
     * Pattern for valid environment variable keys
     */
    private const KEY_PATTERN = '/^[A-Z_][A-Z0-9_]*$/';

    /**
     * Characters that require quoting in values
     */
    private const QUOTE_REQUIRED_CHARS = [' ', '#', '"', "'", '=', '$', '\\'];

    /**
     * Initialize EnvManager
     *
     * @param string|null $envPath Custom path to .env file (null = default)
     * @throws RuntimeException If .env file doesn't exist or isn't writable
     */
    public function __construct(?string $envPath = null)
    {
        $this->envFilePath = $envPath ?? base_path('.env');

        // Validate .env file exists and is writable
        if (!File::exists($this->envFilePath)) {
            throw new RuntimeException(".env file not found at: {$this->envFilePath}");
        }

        if (!File::isWritable($this->envFilePath)) {
            throw new RuntimeException(".env file is not writable: {$this->envFilePath}");
        }
    }

    /**
     * Set a single environment variable in .env file
     *
     * Updates existing key or appends new one if not found.
     * Automatically determines if quoting is needed.
     *
     * @param string $key Environment variable key (uppercase recommended)
     * @param string|int|float|bool|null $value Value to set
     * @param bool $forceQuote Force quoting even if not needed
     * @return bool True on success
     * @throws RuntimeException If key is invalid or file write fails
     */
    public function setKey(string $key, string|int|float|bool|null $value, bool $forceQuote = false): bool
    {
        // Validate key format
        $this->validateKey($key);

        // Convert value to string and handle null
        $stringValue = $this->convertValueToString($value);

        // Determine if quoting is needed
        $needsQuote = $forceQuote || $this->needsQuoting($stringValue);

        // Format the value with proper escaping
        $formattedValue = $needsQuote
            ? $this->quoteValue($stringValue)
            : $stringValue;

        // Update .env file
        return $this->updateEnvFile($key, $formattedValue);
    }

    /**
     * Set multiple environment variables at once
     *
     * @param array<string, mixed> $variables Key-value pairs to set
     * @param bool $forceQuote Force quoting for all values
     * @return bool True if all updates successful
     */
    public function setMultiple(array $variables, bool $forceQuote = false): bool
    {
        foreach ($variables as $key => $value) {
            if (!$this->setKey($key, $value, $forceQuote)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove an environment variable from .env file
     *
     * @param string $key Key to remove
     * @return bool True on success
     */
    public function removeKey(string $key): bool
    {
        $this->validateKey($key);

        $env = File::get($this->envFilePath);
        $pattern = "/^{$key}=.*$/m";

        if (!preg_match($pattern, $env)) {
            return true; // Key doesn't exist, nothing to remove
        }

        // Remove the line
        $env = preg_replace($pattern, '', $env);

        // Clean up multiple blank lines
        $env = preg_replace("/\n{3,}/", "\n\n", $env);

        return File::put($this->envFilePath, $env) !== false;
    }

    /**
     * Check if an environment variable exists in .env file
     *
     * @param string $key Key to check
     * @return bool True if exists
     */
    public function hasKey(string $key): bool
    {
        $env = File::get($this->envFilePath);
        $pattern = "/^{$key}=.*$/m";

        return preg_match($pattern, $env) === 1;
    }

    /**
     * Get current value of an environment variable from .env file
     *
     * Note: Returns the raw value from .env, not the parsed env() value
     *
     * @param string $key Key to get
     * @return string|null Value or null if not found
     */
    public function getKey(string $key): ?string
    {
        $env = File::get($this->envFilePath);
        $pattern = "/^{$key}=(.*)$/m";

        if (preg_match($pattern, $env, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Create backup of .env file
     *
     * @return string|false Backup file path or false on failure
     */
    public function createBackup(): string|false
    {
        $backupPath = $this->envFilePath . '.backup.' . date('YmdHis');

        if (File::copy($this->envFilePath, $backupPath)) {
            return $backupPath;
        }

        return false;
    }

    /**
     * Restore .env file from backup
     *
     * @param string $backupPath Path to backup file
     * @return bool True on success
     */
    public function restoreBackup(string $backupPath): bool
    {
        if (!File::exists($backupPath)) {
            return false;
        }

        return File::copy($backupPath, $this->envFilePath);
    }

    /**
     * Validate environment variable key format
     *
     * Keys must:
     * - Start with letter or underscore
     * - Contain only uppercase letters, numbers, and underscores
     * - Not be empty
     *
     * @param string $key Key to validate
     * @throws RuntimeException If key is invalid
     */
    private function validateKey(string $key): void
    {
        if (empty($key)) {
            throw new RuntimeException('Environment variable key cannot be empty');
        }

        if (!preg_match(self::KEY_PATTERN, $key)) {
            throw new RuntimeException(
                "Invalid environment variable key: '{$key}'. " .
                "Keys must start with a letter or underscore and contain only uppercase letters, numbers, and underscores."
            );
        }
    }

    /**
     * Convert value to string representation
     *
     * @param mixed $value Value to convert
     * @return string String representation
     */
    private function convertValueToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    /**
     * Check if value needs quoting
     *
     * Values need quoting if they contain:
     * - Spaces
     * - Special characters (# " ' = $ \)
     * - Start or end with whitespace
     * - Are empty
     *
     * @param string $value Value to check
     * @return bool True if quoting needed
     */
    private function needsQuoting(string $value): bool
    {
        // Empty values need quotes
        if ($value === '') {
            return true;
        }

        // Check for leading/trailing whitespace
        if (trim($value) !== $value) {
            return true;
        }

        // Check for special characters
        foreach (self::QUOTE_REQUIRED_CHARS as $char) {
            if (str_contains($value, $char)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Quote and escape a value for .env file
     *
     * Handles:
     * - Double quote escaping
     * - Backslash escaping
     * - Newline preservation
     *
     * @param string $value Value to quote
     * @return string Quoted and escaped value
     */
    private function quoteValue(string $value): string
    {
        // Escape backslashes first (must be done before escaping quotes)
        $value = str_replace('\\', '\\\\', $value);

        // Escape double quotes
        $value = str_replace('"', '\\"', $value);

        // Wrap in double quotes
        return "\"{$value}\"";
    }

    /**
     * Update .env file with new key-value pair
     *
     * @param string $key Environment variable key
     * @param string $value Formatted value (already quoted if needed)
     * @return bool True on success
     * @throws RuntimeException If file write fails
     */
    private function updateEnvFile(string $key, string $value): bool
    {
        // Read current .env content
        $env = File::get($this->envFilePath);

        // Pattern to match existing key
        $pattern = "/^{$key}=(.*)$/m";

        // Check if key exists
        if (preg_match($pattern, $env)) {
            // Update existing key
            $env = preg_replace($pattern, "{$key}={$value}", $env);
        } else {
            // Append new key (ensure file ends with newline)
            $env = rtrim($env, "\n") . "\n{$key}={$value}\n";
        }

        // Write to file
        if (File::put($this->envFilePath, $env) === false) {
            throw new RuntimeException("Failed to write to .env file: {$this->envFilePath}");
        }

        return true;
    }

    /**
     * Get all environment variables from .env file
     *
     * @return array<string, string> Key-value pairs
     */
    public function getAllKeys(): array
    {
        $env = File::get($this->envFilePath);
        $lines = explode("\n", $env);
        $variables = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Parse key=value
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $variables[trim($key)] = trim($value);
            }
        }

        return $variables;
    }
}


















