<?php

declare(strict_types=1);

/**
 * Simple key/value site settings model.
 */
final class SiteSetting
{
    /**
     * @param array<int, string> $keys
     * @return array<string, string>
     */
    public static function getMany(array $keys): array
    {
        $keys = array_values(array_filter(array_unique($keys), static fn ($key) => trim((string) $key) !== ''));

        if ($keys === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));

        $stmt = db()->prepare("
            SELECT setting_key, setting_value
            FROM site_settings
            WHERE setting_key IN ({$placeholders})
        ");

        $stmt->execute($keys);

        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
        }

        return $settings;
    }

    /**
     * @param array<string, string> $settings
     */
    public static function upsertMany(array $settings): void
    {
        $stmt = db()->prepare("
            INSERT INTO site_settings (setting_key, setting_value)
            VALUES (:setting_key, :setting_value)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settings as $key => $value) {
            $stmt->execute([
                ':setting_key' => (string) $key,
                ':setting_value' => (string) $value,
            ]);
        }
    }
}
