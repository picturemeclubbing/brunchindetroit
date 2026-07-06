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
                v.hero_blurb,
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
                v.profile_tier,
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
     * slider (Phase 5C). Additive only - does not affect published(),
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
                v.hero_blurb,
                v.main_image_path,
                v.price_range,
                v.brunch_hours_note,
                v.website_url,
                v.is_featured,
                v.featured_sort,
                v.profile_tier,
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
                v.hero_blurb,
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
                v.profile_tier,
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
    // Phase 5B - admin CRUD methods (do not affect public reads above).
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
                v.hero_blurb,
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
                v.profile_tier,
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
                v.hero_blurb,
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
                v.profile_tier,
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
     * Structured brunch hours for a venue, one row per open day
     * (hour_type = 'brunch'), used to validate RSVP requested date/time.
     *
     * Returns an empty array if the venue has no rows in venue_hours yet
     * (the table exists in the schema but nothing currently writes to it —
     * there is no admin UI for it). Callers must treat an empty result as
     * "unknown / not yet configured", not as "closed every day".
     *
     * @return array<int, array{day_of_week: int, open_time: ?string, close_time: ?string, is_closed: int}>
     */
    public static function brunchHoursForVenue(int $venueId): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT day_of_week, open_time, close_time, is_closed
            FROM venue_hours
            WHERE venue_id = :venue_id AND hour_type = 'brunch'
            ORDER BY day_of_week ASC
        ");
        $stmt->execute([':venue_id' => $venueId]);

        return $stmt->fetchAll();
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
                (slug, name, description, hero_blurb, address_line1, address_line2, city, state, zip,
                 phone, website_url, instagram_url, facebook_url, neighborhood_id,
                 price_range, brunch_hours_note, main_image_path,
                 is_published, is_featured, featured_sort, profile_tier)
             VALUES
                (:slug, :name, :description, :hero_blurb, :address_line1, :address_line2, :city, :state, :zip,
                 :phone, :website_url, :instagram_url, :facebook_url, :neighborhood_id,
                 :price_range, :brunch_hours_note, :main_image_path,
                 :is_published, :is_featured, :featured_sort, :profile_tier)'
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
                hero_blurb = :hero_blurb,
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
                featured_sort = :featured_sort,
                profile_tier = :profile_tier
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

        // city/state are NOT NULL columns with defaults - never bind null.
        $city  = $nullable($data['city'] ?? '') ?? 'Detroit';
        $state = $nullable($data['state'] ?? '') ?? 'MI';

        $profileTier = (string) ($data['profile_tier'] ?? 'free');
        if (!in_array($profileTier, ['free', 'premium'], true)) {
            $profileTier = 'free';
        }

        return [
            ':slug'              => (string) ($data['slug'] ?? ''),
            ':name'              => (string) ($data['name'] ?? ''),
            ':description'       => $nullable($data['description'] ?? null),
            ':hero_blurb'        => $nullable($data['hero_blurb'] ?? null),
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
            ':profile_tier'      => $profileTier,
        ];
    }

    /**
     * Admin: all neighborhoods with assigned venue counts.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function neighborhoodsWithCounts(): array
    {
        $pdo = db();

        $sql = "
            SELECT
                n.id,
                n.name,
                n.slug,
                n.sort_order,
                n.is_active,
                COUNT(v.id) AS venue_count
            FROM neighborhoods n
            LEFT JOIN venues v ON v.neighborhood_id = n.id
            GROUP BY n.id, n.name, n.slug, n.sort_order, n.is_active
            ORDER BY n.sort_order ASC, n.name ASC
        ";

        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Admin: find one neighborhood by id.
     *
     * @return array<string, mixed>|null
     */
    public static function neighborhoodFind(int $id): ?array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT id, name, slug, sort_order, is_active
            FROM neighborhoods
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public static function neighborhoodSlugExists(string $slug, ?int $ignoreId = null): bool
    {
        $pdo = db();

        $sql = "
            SELECT COUNT(*)
            FROM neighborhoods
            WHERE slug = :slug
        ";

        if ($ignoreId !== null) {
            $sql .= " AND id <> :ignore_id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);

        if ($ignoreId !== null) {
            $stmt->bindValue(':ignore_id', $ignoreId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function neighborhoodCreate(array $data): int
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            INSERT INTO neighborhoods (name, slug, sort_order, is_active)
            VALUES (:name, :slug, :sort_order, :is_active)
        ");

        $stmt->bindValue(':name', (string) ($data['name'] ?? ''));
        $stmt->bindValue(':slug', (string) ($data['slug'] ?? ''));
        $stmt->bindValue(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':is_active', !empty($data['is_active']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public static function neighborhoodUpdate(int $id, array $data): bool
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            UPDATE neighborhoods
            SET
                name = :name,
                slug = :slug,
                sort_order = :sort_order,
                is_active = :is_active
            WHERE id = :id
        ");

        $stmt->bindValue(':name', (string) ($data['name'] ?? ''));
        $stmt->bindValue(':slug', (string) ($data['slug'] ?? ''));
        $stmt->bindValue(':sort_order', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':is_active', !empty($data['is_active']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function neighborhoodVenueCount(int $id): int
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM venues
            WHERE neighborhood_id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function neighborhoodDelete(int $id): bool
    {
        if (self::neighborhoodVenueCount($id) > 0) {
            throw new RuntimeException('This neighborhood has venues assigned to it and cannot be deleted.');
        }

        $pdo = db();

        $stmt = $pdo->prepare("
            DELETE FROM neighborhoods
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Interior/profile images for a venue, ordered for display.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function imagesForVenue(int $venueId, int $limit = 4): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT id, venue_id, file_path, caption, sort_order
            FROM venue_images
            WHERE venue_id = :venue_id
            ORDER BY sort_order ASC, id ASC
            LIMIT :limit
        ");

        $stmt->bindValue(':venue_id', $venueId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Replace the admin-managed interior/profile image URLs for a venue.
     *
     * @param array<int, array{file_path:string, caption:string, sort_order:int}> $images
     */
    public static function replaceImages(int $venueId, array $images): void
    {
        $pdo = db();

        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare('DELETE FROM venue_images WHERE venue_id = :venue_id');
            $delete->execute([':venue_id' => $venueId]);

            $insert = $pdo->prepare(
                'INSERT INTO venue_images (venue_id, file_path, caption, sort_order)
                 VALUES (:venue_id, :file_path, :caption, :sort_order)'
            );

            foreach ($images as $image) {
                $filePath = trim((string) ($image['file_path'] ?? ''));
                if ($filePath === '') {
                    continue;
                }

                $caption = trim((string) ($image['caption'] ?? ''));

                $insert->execute([
                    ':venue_id'   => $venueId,
                    ':file_path'  => $filePath,
                    ':caption'    => $caption !== '' ? $caption : null,
                    ':sort_order' => (int) ($image['sort_order'] ?? 0),
                ]);
            }

            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }
    /**
     * Nearby published venues for the public venue profile.
     *
     * Same neighborhood ranks first, then same city. Current venue excluded.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function nearbyForVenue(int $venueId, int $limit = 3): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT
                v.id,
                v.slug,
                v.name,
                v.description,
                v.hero_blurb,
                v.address_line1,
                v.address_line2,
                v.city,
                v.state,
                v.zip,
                v.phone,
                v.price_range,
                v.brunch_hours_note,
                v.main_image_path,
                v.is_featured,
                v.featured_sort,
                v.profile_tier,
                n.name AS neighborhood_name,
                CASE
                    WHEN cur.neighborhood_id IS NOT NULL AND v.neighborhood_id = cur.neighborhood_id THEN 0
                    WHEN cur.city IS NOT NULL AND cur.city <> '' AND LOWER(v.city) = LOWER(cur.city) THEN 1
                    ELSE 2
                END AS nearby_rank
            FROM venues v
            INNER JOIN venues cur ON cur.id = :venue_id
            LEFT JOIN neighborhoods n ON n.id = v.neighborhood_id
            WHERE v.is_published = 1
              AND v.id <> cur.id
              AND (
                    (cur.neighborhood_id IS NOT NULL AND v.neighborhood_id = cur.neighborhood_id)
                    OR (cur.city IS NOT NULL AND cur.city <> '' AND LOWER(v.city) = LOWER(cur.city))
              )
            ORDER BY nearby_rank ASC, v.is_featured DESC, COALESCE(v.featured_sort, 999), v.name ASC
            LIMIT :limit
        ");

        $stmt->bindValue(':venue_id', $venueId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
