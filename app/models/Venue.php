<?php
declare(strict_types=1);

final class Venue
{
    public static function published(): array
    {
        $pdo = db();

        $sql = "
            SELECT
                v.id,
                v.slug,
                v.name,
                v.description,
                v.address_line1,
                v.address_line2,
                v.city,
                v.state,
                v.zip,
                v.phone,
                v.website_url,
                v.instagram_url,
                v.facebook_url,
                v.price_range,
                v.brunch_hours_note,
                v.main_image_path,
                v.is_featured,
                v.featured_sort,
                v.updated_at,
                n.name AS neighborhood_name
            FROM venues v
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE v.is_published = 1
            ORDER BY v.is_featured DESC, COALESCE(v.featured_sort, 999), v.name ASC
        ";

        $stmt = $pdo->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Fetch a single published venue by its slug.
     *
     * Returns the same field set as published() (including the joined
     * neighborhood name) or null when no published venue matches.
     */
    public static function findBySlug(string $slug): ?array
    {
        $pdo = db();

        $sql = "
            SELECT
                v.id,
                v.slug,
                v.name,
                v.description,
                v.address_line1,
                v.address_line2,
                v.city,
                v.state,
                v.zip,
                v.phone,
                v.website_url,
                v.instagram_url,
                v.facebook_url,
                v.price_range,
                v.brunch_hours_note,
                v.main_image_path,
                v.is_featured,
                v.featured_sort,
                v.updated_at,
                n.name AS neighborhood_name
            FROM venues v
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE v.is_published = 1
              AND v.slug = :slug
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':slug' => $slug]);

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }
}
