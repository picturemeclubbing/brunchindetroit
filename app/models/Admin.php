<?php

declare(strict_types=1);

require_once APP_ROOT . '/config/database.php';

class Admin
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, email, password_hash, display_name FROM admins WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function updateLastLogin(int $id): void
    {
        $stmt = db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

class DashboardStats
{
    public static function counts(): array
    {
        $pdo = db();

        return [
            'venues' => self::scalar($pdo, 'SELECT COUNT(*) FROM venues'),
            'venues_published' => self::scalar($pdo, 'SELECT COUNT(*) FROM venues WHERE is_published = 1'),
            'blog_posts' => self::scalar($pdo, 'SELECT COUNT(*) FROM blog_posts'),
            'galleries' => self::scalar($pdo, 'SELECT COUNT(*) FROM galleries'),
            'menu_items' => self::scalar($pdo, 'SELECT COUNT(*) FROM menu_items'),
        ];
    }

    private static function scalar(PDO $pdo, string $sql): int
    {
        return (int) $pdo->query($sql)->fetchColumn();
    }
}
