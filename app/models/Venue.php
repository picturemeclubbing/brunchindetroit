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
     * Featured, published venues for the Home page "Featured Brunch Spots"
     * slider (Phase 5C). Additive only — does not affect published(),
     * findBySlug(), or admin methods.
     *
     * Returns published venues where is_featured = 1, ordered by
     * featured_sort ASC then name ASC so admins control home placement via
     * the Featured toggle + Featured Sort field.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function featuredForHome(int $limit = 6): array
    {
        $pdo = db();

        $sql = "
            SELECT
                v.slug,
                v.name,
                v.description,
                v.main_image_path,
                v.price_range,
                v.brunch_hours_note,
                v.website_url,
                v.is_featured,
                v.featured_sort,
                n.name AS neighborhood_name
            FROM venues v
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE v.is_published = 1
              AND v.is_featured = 1
            ORDER BY v.featured_sort ASC, v.name ASC
            LIMIT :limit
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

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

    /**
     * All venues (including unpublished) as id/name pairs, for admin
     * dropdowns. Ordered alphabetically by name.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function allForSelect(): array
    {
        $pdo = db();

        $sql = '
            SELECT id, name
            FROM venues
            ORDER BY name ASC
        ';

        return $pdo->query($sql)->fetchAll();
    }

    // --------------------------------------------------------------------------
    // Phase 5B — admin CRUD methods (do not affect public reads above).
    // --------------------------------------------------------------------------

    /**
     * All venues for the admin list (including unpublished), with neighborhood
     * name. Ordered newest-first so admins see recent work at the top.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
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
                v.neighborhood_id,
                v.price_range,
                v.brunch_hours_note,
                v.main_image_path,
                v.is_published,
                v.is_featured,
                v.featured_sort,
                v.menu_last_updated_at,
                v.published_at,
                v.created_at,
                v.updated_at,
                n.name AS neighborhood_name
            FROM venues v
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            ORDER BY v.created_at DESC
        ";

        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Single venue by primary key (including unpublished), with neighborhood
     * name.
     *
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
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
                v.neighborhood_id,
                v.price_range,
                v.brunch_hours_note,
                v.main_image_path,
                v.is_published,
                v.is_featured,
                v.featured_sort,
                v.menu_last_updated_at,
                v.published_at,
                v.created_at,
                v.updated_at,
                n.name AS neighborhood_name
            FROM venues v
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE v.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Whether a slug is already in use by another venue.
     *
     * Pass the current record id (when editing) as $ignoreId so the venue
     * being saved is excluded from the uniqueness check.
     */
    public static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $pdo = db();

        $sql = 'SELECT COUNT(*) FROM venues WHERE slug = :slug';
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
     * Insert a venue row.
     *
     * Pass empty optional fields as null (not ''). Booleans may be passed as
     * bool/int; they are normalized to 0/1 here. city/state fall back to the
     * table defaults when blank because those columns are NOT NULL.
     *
     * @param array<string, mixed> $data
     * @return int New venue id.
     */
    public static function create(array $data): int
    {
        $pdo = db();

        $stmt = $pdo->prepare(
            'INSERT INTO venues
                (slug, name, description, address_line1, address_line2, city, state, zip,
                 phone, website_url, instagram_url, facebook_url, neighborhood_id,
                 price_range, brunch_hours_note, main_image_path,
                 is_published, is_featured, featured_sort)
             VALUES
                (:slug, :name, :description, :address_line1, :address_line2, :city, :state, :zip,
                 :phone, :website_url, :instagram_url, :facebook_url, :neighborhood_id,
                 :price_range, :brunch_hours_note, :main_image_path,
                 :is_published, :is_featured, :featured_sort)'
        );

        $stmt->execute(self::bindValues($data));

        return (int) $pdo->lastInsertId();
    }

    /**
     * Update an existing venue row. updated_at is refreshed automatically by
     * the table's ON UPDATE CURRENT_TIMESTAMP.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        $pdo = db();

        $stmt = $pdo->prepare(
            'UPDATE venues SET
                slug = :slug,
                name = :name,
                description = :description,
                address_line1 = :address_line1,
                address_line2 = :address_line2,
                city = :city,
                state = :state,
                zip = :zip,
                phone = :phone,
                website_url = :website_url,
                instagram_url = :instagram_url,
                facebook_url = :facebook_url,
                neighborhood_id = :neighborhood_id,
                price_range = :price_range,
                brunch_hours_note = :brunch_hours_note,
                main_image_path = :main_image_path,
                is_published = :is_published,
                is_featured = :is_featured,
                featured_sort = :featured_sort
             WHERE id = :id'
        );

        $values = self::bindValues($data);
        $values[':id'] = $id;

        $stmt->execute($values);
    }

    /**
     * Toggle the published flag for a venue.
     */
    public static function setPublished(int $id, bool $published): void
    {
        $pdo = db();

        $stmt = $pdo->prepare('UPDATE venues SET is_published = :value WHERE id = :id');
        $stmt->execute([
            ':id'    => $id,
            ':value' => $published ? 1 : 0,
        ]);
    }

    /**
     * Toggle the featured flag for a venue.
     */
    public static function setFeatured(int $id, bool $featured): void
    {
        $pdo = db();

        $stmt = $pdo->prepare('UPDATE venues SET is_featured = :value WHERE id = :id');
        $stmt->execute([
            ':id'    => $id,
            ':value' => $featured ? 1 : 0,
        ]);
    }

    /**
     * Active neighborhoods as id/name pairs for admin dropdowns, ordered by
     * sort_order then name.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function neighborhoodsForSelect(): array
    {
        $pdo = db();

        $sql = '
            SELECT id, name
            FROM neighborhoods
            WHERE is_active = 1
            ORDER BY sort_order ASC, name ASC
        ';

        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Normalize the Phase 5B field set into bind-ready values.
     *
     * - Empty nullable strings become null.
     * - neighborhood_id becomes null or a positive int.
     * - city/state fall back to the table defaults ('Detroit'/'MI') when blank
     *   because those columns are NOT NULL.
     * - Booleans become 0/1.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function bindValues(array $data): array
    {
        $nullable = static function ($value) {
            return ($value === null || $value === '') ? null : $value;
        };

        $neighborhoodId = null;
        if (isset($data['neighborhood_id']) && $data['neighborhood_id'] !== '' && $data['neighborhood_id'] !== null) {
            $candidate = is_numeric($data['neighborhood_id']) ? (int) $data['neighborhood_id'] : 0;
            $neighborhoodId = $candidate > 0 ? $candidate : null;
        }

        // city/state are NOT NULL columns with defaults — never bind null.
        $city  = $nullable($data['city'] ?? '') ?? 'Detroit';
        $state = $nullable($data['state'] ?? '') ?? 'MI';

        return [
            ':slug'              => (string) ($data['slug'] ?? ''),
            ':name'              => (string) ($data['name'] ?? ''),
            ':description'       => $nullable($data['description'] ?? null),
            ':address_line1'     => $nullable($data['address_line1'] ?? null),
            ':address_line2'     => $nullable($data['address_line2'] ?? null),
            ':city'              => $city,
            ':state'             => $state,
            ':zip'               => $nullable($data['zip'] ?? null),
            ':phone'             => $nullable($data['phone'] ?? null),
            ':website_url'       => $nullable($data['website_url'] ?? null),
            ':instagram_url'     => $nullable($data['instagram_url'] ?? null),
            ':facebook_url'      => $nullable($data['facebook_url'] ?? null),
            ':neighborhood_id'   => $neighborhoodId,
            ':price_range'       => $nullable($data['price_range'] ?? null),
            ':brunch_hours_note' => $nullable($data['brunch_hours_note'] ?? null),
            ':main_image_path'   => $nullable($data['main_image_path'] ?? null),
            ':is_published'      => !empty($data['is_published']) ? 1 : 0,
            ':is_featured'       => !empty($data['is_featured']) ? 1 : 0,
            ':featured_sort'     => (int) ($data['featured_sort'] ?? 0),
        ];
    }
}
