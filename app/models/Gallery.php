<?php

declare(strict_types=1);

/**
 * Gallery model (Phase 4B public reads + Phase 5A admin CRUD).
 *
 * Provides published galleries for the public Gallery index. Galleries link
 * directly to an external gallery_url (SmugMug, etc.); this model does NOT
 * load local images or query gallery_images.
 *
 * Phase 5A adds admin read/write methods (all/find/slugExists/create/update/
 * setPublished/setFeatured) without changing the public methods.
 *
 * Uses PDO through db() and prepared statements. Public queries LEFT JOIN
 * venues and neighborhoods; admin queries LEFT JOIN venues for the name.
 */
final class Gallery
{
    /**
     * Published galleries, with venue + neighborhood names.
     *
     * @param string|null $q        Free-text search over title, description,
     *                              location_label, and venue name.
     * @param string|null $location Location filter over location_label,
     *                              venue name, or neighborhood name.
     * @param int|null    $year     Optional event_date year filter.
     * @param int|null        Optional event_date month filter (1-12).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function publishedGalleries(
        ?string $q = null,
        ?string $location = null,
        ?int $year = null,
        ?int $month = null
    ): array {
        $pdo = db();

        $sql = "
            SELECT
                g.id,
                g.slug,
                g.title,
                g.description,
                g.cover_image_path,
                g.gallery_url,
                g.event_date,
                g.location_label,
                g.is_featured,
                v.name AS venue_name,
                n.name AS neighborhood_name
            FROM galleries g
            LEFT JOIN venues v        ON v.id = g.venue_id
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE g.is_published = 1
        ";

        $params = [];

        if ($q !== null && $q !== '') {
            $sql .= " AND (
                g.title LIKE :q
                OR g.description LIKE :q
                OR g.location_label LIKE :q
                OR v.name LIKE :q
            )";
            $params[':q'] = '%' . $q . '%';
        }

        if ($location !== null && $location !== '') {
            $sql .= " AND (
                g.location_label LIKE :location_label
                OR v.name LIKE :location_venue
                OR n.name LIKE :location_neighborhood
            )";
            $locationLike = '%' . $location . '%';
            $params[':location_label'] = $locationLike;
            $params[':location_venue'] = $locationLike;
            $params[':location_neighborhood'] = $locationLike;
        }

        if ($year !== null && $year > 0) {
            $sql .= " AND g.event_date IS NOT NULL AND YEAR(g.event_date) = :year";
            $params[':year'] = $year;
        }

        if ($month !== null && $month >= 1 && $month <= 12) {
            $sql .= " AND g.event_date IS NOT NULL AND MONTH(g.event_date) = :month";
            $params[':month'] = $month;
        }

        // Featured first, then newest event_date, then newest created_at.
        $sql .= "
            ORDER BY g.is_featured DESC,
                     g.event_date DESC,
                     g.created_at DESC
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Up to $limit published galleries for the home Featured Spotlight slider.
     * Prefers featured galleries first, then newest event_date/created_at.
     * Read-only, additive.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function featuredForHome(int $limit = 2): array
    {
        $pdo = db();

        $sql = "
            SELECT
                g.id,
                g.slug,
                g.title,
                g.description,
                g.cover_image_path,
                g.gallery_url,
                g.event_date,
                g.location_label,
                g.is_featured,
                v.name AS venue_name,
                n.name AS neighborhood_name
            FROM galleries g
            LEFT JOIN venues v        ON v.id = g.venue_id
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE g.is_published = 1
            ORDER BY g.is_featured DESC,
                     g.event_date DESC,
                     g.created_at DESC
            LIMIT :limit
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Distinct location labels available for the filter dropdown.
     * Combines galleries.location_label and neighborhood names from venues.
     *
     * @return array<int, string>
     */
    public static function availableLocations(): array
    {
        $pdo = db();

        // Union of populated location_labels and neighborhood names for
        // published galleries. De-duplicated and sorted alphabetically.
        $sql = "
            SELECT label FROM (
                SELECT g.location_label AS label
                FROM galleries g
                WHERE g.is_published = 1
                  AND g.location_label IS NOT NULL
                  AND TRIM(g.location_label) <> ''
                UNION
                SELECT n.name AS label
                FROM galleries g
                INNER JOIN venues v        ON v.id = g.venue_id
                INNER JOIN neighborhoods n ON n.id = v.neighborhood_id
                WHERE g.is_published = 1
                  AND n.name IS NOT NULL
                  AND TRIM(n.name) <> ''
            ) AS combined
            WHERE label IS NOT NULL AND TRIM(label) <> ''
            ORDER BY label ASC
        ";

        $rows = $pdo->query($sql)->fetchAll();

        $labels = [];
        foreach ($rows as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            if ($label !== '' && !in_array($label, $labels, true)) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * Distinct years (DESC) derived from published galleries' event_date.
     *
     * @return array<int, int>
     */
    public static function availableYears(): array
    {
        $pdo = db();

        $sql = "
            SELECT DISTINCT YEAR(g.event_date) AS yr
            FROM galleries g
            WHERE g.is_published = 1
              AND g.event_date IS NOT NULL
            ORDER BY yr DESC
        ";

        $rows = $pdo->query($sql)->fetchAll();

        $years = [];
        foreach ($rows as $row) {
            $year = (int) ($row['yr'] ?? 0);
            if ($year > 0) {
                $years[] = $year;
            }
        }

        return $years;
    }

    // --------------------------------------------------------------------------
    // Phase 5A - admin CRUD methods (do not affect public reads above).
    // --------------------------------------------------------------------------

    /**
     * All galleries for the admin list (including unpublished), with venue
     * name. Ordered newest-first so admins see recent work at the top.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $pdo = db();

        $sql = "
            SELECT
                g.id,
                g.slug,
                g.title,
                g.venue_id,
                g.event_date,
                g.location_label,
                g.description,
                g.cover_image_path,
                g.gallery_url,
                g.is_published,
                g.is_featured,
                g.created_at,
                g.updated_at,
                v.name AS venue_name
            FROM galleries g
            LEFT JOIN venues v ON v.id = g.venue_id
            ORDER BY g.created_at DESC
        ";

        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Single gallery by primary key (including unpublished), with venue name.
     *
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $pdo = db();

        $sql = "
            SELECT
                g.id,
                g.slug,
                g.title,
                g.venue_id,
                g.event_date,
                g.location_label,
                g.description,
                g.cover_image_path,
                g.gallery_url,
                g.is_published,
                g.is_featured,
                g.created_at,
                g.updated_at,
                v.name AS venue_name
            FROM galleries g
            LEFT JOIN venues v ON v.id = g.venue_id
            WHERE g.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Single published gallery by slug for public landing/ad-wall pages.
     *
     * @return array<string, mixed>|null
     */
    public static function findPublishedBySlug(string $slug): ?array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT
                g.id,
                g.slug,
                g.title,
                g.description,
                g.cover_image_path,
                g.gallery_url,
                g.event_date,
                g.location_label,
                g.is_featured,
                v.name AS venue_name,
                n.name AS neighborhood_name
            FROM galleries g
            LEFT JOIN venues v        ON v.id = g.venue_id
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE g.is_published = 1
              AND g.slug = :slug
            LIMIT 1
        ");

        $stmt->execute([':slug' => $slug]);

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Whether a slug is already in use by another gallery.
     *
     * Pass the current record id (when editing) as $ignoreId so the gallery
     * being saved is excluded from the uniqueness check.
     */
    public static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $pdo = db();

        $sql = 'SELECT COUNT(*) FROM galleries WHERE slug = :slug';
        $params = [':slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $ignoreId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Insert a gallery row.
     *
     * Callers should pass empty optional fields as null (not ''). Booleans may
     * be passed as bool/int; they are normalized to 0/1 here.
     *
     * @param array<string, mixed> $data
     * @return int New gallery id.
     */
    public static function create(array $data): int
    {
        $pdo = db();

        $stmt = $pdo->prepare(
            'INSERT INTO galleries
                (slug, title, venue_id, event_date, location_label, description,
                 cover_image_path, gallery_url, is_published, is_featured)
             VALUES
                (:slug, :title, :venue_id, :event_date, :location_label, :description,
                 :cover_image_path, :gallery_url, :is_published, :is_featured)'
        );

        $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':venue_id' => $data['venue_id'] ?? null,
            ':event_date' => $data['event_date'] ?? null,
            ':location_label' => $data['location_label'] ?? null,
            ':description' => $data['description'] ?? null,
            ':cover_image_path' => $data['cover_image_path'] ?? null,
            ':gallery_url' => $data['gallery_url'] ?? null,
            ':is_published' => !empty($data['is_published']) ? 1 : 0,
            ':is_featured' => !empty($data['is_featured']) ? 1 : 0,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Update an existing gallery row. updated_at is refreshed automatically by
     * the table's ON UPDATE CURRENT_TIMESTAMP.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        $pdo = db();

        $stmt = $pdo->prepare(
            'UPDATE galleries SET
                slug = :slug,
                title = :title,
                venue_id = :venue_id,
                event_date = :event_date,
                location_label = :location_label,
                description = :description,
                cover_image_path = :cover_image_path,
                gallery_url = :gallery_url,
                is_published = :is_published,
                is_featured = :is_featured
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':venue_id' => $data['venue_id'] ?? null,
            ':event_date' => $data['event_date'] ?? null,
            ':location_label' => $data['location_label'] ?? null,
            ':description' => $data['description'] ?? null,
            ':cover_image_path' => $data['cover_image_path'] ?? null,
            ':gallery_url' => $data['gallery_url'] ?? null,
            ':is_published' => !empty($data['is_published']) ? 1 : 0,
            ':is_featured' => !empty($data['is_featured']) ? 1 : 0,
        ]);
    }

    /**
     * Toggle the published flag for a gallery.
     */
    public static function setPublished(int $id, bool $published): void
    {
        $pdo = db();

        $stmt = $pdo->prepare('UPDATE galleries SET is_published = :value WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':value' => $published ? 1 : 0,
        ]);
    }

    /**
     * Toggle the featured flag for a gallery.
     */
    public static function setFeatured(int $id, bool $featured): void
    {
        $pdo = db();

        $stmt = $pdo->prepare('UPDATE galleries SET is_featured = :value WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':value' => $featured ? 1 : 0,
        ]);
    }


    /**
     * Most recent published gallery for a venue.
     *
     * @return array<string, mixed>|null
     */
    public static function recentForVenue(int $venueId): ?array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT
                g.id,
                g.slug,
                g.title,
                g.description,
                g.cover_image_path,
                g.gallery_url,
                g.event_date,
                g.location_label,
                g.is_featured,
                v.name AS venue_name,
                n.name AS neighborhood_name
            FROM galleries g
            LEFT JOIN venues v        ON v.id = g.venue_id
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE g.is_published = 1
              AND g.venue_id = :venue_id
            ORDER BY
                g.event_date DESC,
                g.created_at DESC
            LIMIT 1
        ");

        $stmt->bindValue(':venue_id', $venueId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }
}
