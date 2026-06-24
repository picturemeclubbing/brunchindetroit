<?php

declare(strict_types=1);

/**
 * Read-only Menu model (Phase 3C).
 *
 * Provides allergen list and venue-scoped menu categories/items with
 * allergy-aware filtering. Uses PDO through db() and prepared statements.
 *
 * Filtering rule: when an allergen slug is supplied, only menu items that
 * have an explicit status of 'does_not_contain' for that allergen are kept.
 * Items with any other status (contains / may_contain / cross_contact_risk /
 * unknown) ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â or no status row at all ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â are excluded.
 */
final class Menu
{
    /**
     * All allergens, ordered by name.
     *
     * @return array<int, array{id:int, name:string, slug:string}>
     */
    public static function allergens(): array
    {
        $pdo = db();

        $sql = "
            SELECT id, name, slug
            FROM allergens
            ORDER BY name ASC
        ";

        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Menu categories (with their published items) for a venue.
     *
     * When $allergenSlug is null/empty, every published item is returned.
     * When provided, only items whose allergen status is exactly
     * 'does_not_contain' for the matching allergen are returned; items with
     * any other status or no status row are excluded. Categories with no
     * matching items are omitted from the result.
     *
     * @return array<int, array{
     *     id:int,
     *     name:string,
     *     sort_order:int,
     *     items:array<int, array{
     *         id:int,
     *         name:string,
     *         description:?string,
     *         price:?string,
     *         category_id:?int,
     *         sort_order:int,
     *         allergen_statuses:array<string,string>
     *     }>
     * }>
     */
    public static function categoriesWithItemsForVenue(int $venueId, ?string $allergenSlug = null): array
    {
        $pdo = db();

        // Normalize the filter slug. If it doesn't resolve to a known allergen,
        // treat it as "no filter" rather than returning an empty page.
        $allergenSlug = trim((string) $allergenSlug);
        $filterAllergenId = null;
        if ($allergenSlug !== '') {
            $filterAllergenId = self::allergenIdBySlug($allergenSlug);
            if ($filterAllergenId === null) {
                $allergenSlug = '';
            }
        }

        // Fetch published items for the venue, applying the allergen filter
        // as a JOIN condition when an active filter is present.
        if ($filterAllergenId !== null) {
            // Only items with status = 'does_not_contain' for the selected allergen.
            // INNER JOIN deliberately excludes items with no status row.
            $itemsSql = "
                SELECT
                    mi.id,
                    mi.name,
                    mi.description,
                    mi.image_url,
                    mi.image_alt_text,
                    mi.price,
                    mi.category_id,
                    mi.sort_order
                FROM menu_items mi
                INNER JOIN menu_item_allergen_statuses mias
                    ON mias.menu_item_id = mi.id
                   AND mias.allergen_id = :allergen_id
                   AND mias.status = 'does_not_contain'
                WHERE mi.venue_id = :venue_id
                  AND mi.is_published = 1
                ORDER BY mi.category_id ASC, mi.sort_order ASC, mi.name ASC
            ";
            $itemsStmt = $pdo->prepare($itemsSql);
            $itemsStmt->execute([
                ':venue_id'    => $venueId,
                ':allergen_id' => $filterAllergenId,
            ]);
        } else {
            $itemsSql = "
                SELECT
                    mi.id,
                    mi.name,
                    mi.description,
                    mi.image_url,
                    mi.image_alt_text,
                    mi.price,
                    mi.category_id,
                    mi.sort_order
                FROM menu_items mi
                WHERE mi.venue_id = :venue_id
                  AND mi.is_published = 1
                ORDER BY mi.category_id ASC, mi.sort_order ASC, mi.name ASC
            ";
            $itemsStmt = $pdo->prepare($itemsSql);
            $itemsStmt->execute([':venue_id' => $venueId]);
        }

        $items = $itemsStmt->fetchAll();

        // Bail early if there are no items (avoids unnecessary queries below).
        if ($items === []) {
            return [];
        }

        // Load all allergen statuses for the fetched items (keyed by item id)
        // so the view can display per-item badges without extra queries.
        $itemIds = array_map(static fn ($row) => (int) $row['id'], $items);
        $statusesByItem = self::allergenStatusesForItems($itemIds);
        $dietaryTagsByItem = self::dietaryTagsForItems($itemIds);

        // Fetch the venue's categories, ordered for display.
        $catsSql = "
            SELECT id, name, sort_order
            FROM menu_categories
            WHERE venue_id = :venue_id
            ORDER BY sort_order ASC, name ASC
        ";
        $catsStmt = $pdo->prepare($catsSql);
        $catsStmt->execute([':venue_id' => $venueId]);
        $categories = $catsStmt->fetchAll();

        // Index categories by id and attach matching items.
        $byId = [];
        foreach ($categories as $cat) {
            $catId = (int) $cat['id'];
            $byId[$catId] = [
                'id'         => $catId,
                'name'       => (string) $cat['name'],
                'sort_order' => (int) $cat['sort_order'],
                'items'      => [],
            ];
        }

        foreach ($items as $item) {
            $categoryId = $item['category_id'] !== null ? (int) $item['category_id'] : null;

            // Item with no category: skip (we only render categorized menus).
            if ($categoryId === null || !isset($byId[$categoryId])) {
                continue;
            }

            $itemId = (int) $item['id'];
            $byId[$categoryId]['items'][] = [
                'id'                => $itemId,
                'name'              => (string) $item['name'],
                'description'       => $item['description'] !== null ? (string) $item['description'] : null,
                'image_url'         => $item['image_url'] !== null ? (string) $item['image_url'] : null,
                'image_alt_text'    => $item['image_alt_text'] !== null ? (string) $item['image_alt_text'] : null,
                'price'             => $item['price'] !== null ? (string) $item['price'] : null,
                'category_id'       => $categoryId,
                'sort_order'        => (int) $item['sort_order'],
                'allergen_statuses' => $statusesByItem[$itemId] ?? [],
                'dietary_tags'      => $dietaryTagsByItem[$itemId] ?? [],
            ];
        }

        // Drop categories that ended up with no items after filtering.
        return array_values(array_filter($byId, static fn ($cat) => $cat['items'] !== []));
    }

    /**
     * Admin: categories for a venue with item counts.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function adminCategoriesForVenue(int $venueId): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT
                mc.id,
                mc.venue_id,
                mc.name,
                mc.sort_order,
                COUNT(mi.id) AS item_count
            FROM menu_categories mc
            LEFT JOIN menu_items mi ON mi.category_id = mc.id
            WHERE mc.venue_id = :venue_id
            GROUP BY mc.id, mc.venue_id, mc.name, mc.sort_order
            ORDER BY mc.sort_order ASC, mc.name ASC
        ");
        $stmt->execute([':venue_id' => $venueId]);

        return $stmt->fetchAll();
    }

    /**
     * Admin: all menu items for a venue.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function adminItemsForVenue(int $venueId): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT
                mi.id,
                mi.venue_id,
                mi.category_id,
                mi.name,
                mi.description,
                mi.image_url,
                mi.image_alt_text,
                mi.price,
                mi.sort_order,
                mi.is_published,
                mc.name AS category_name
            FROM menu_items mi
            LEFT JOIN menu_categories mc ON mc.id = mi.category_id
            WHERE mi.venue_id = :venue_id
            ORDER BY mc.sort_order ASC, mc.name ASC, mi.sort_order ASC, mi.name ASC
        ");
        $stmt->execute([':venue_id' => $venueId]);

        return $stmt->fetchAll();
    }

    public static function findCategory(int $id): ?array
    {
        $pdo = db();

        $stmt = $pdo->prepare("SELECT * FROM menu_categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public static function createCategory(array $data): int
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            INSERT INTO menu_categories (venue_id, name, sort_order)
            VALUES (:venue_id, :name, :sort_order)
        ");
        $stmt->execute([
            ':venue_id' => (int) $data['venue_id'],
            ':name' => (string) $data['name'],
            ':sort_order' => (int) $data['sort_order'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function updateCategory(int $id, array $data): void
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            UPDATE menu_categories
            SET name = :name,
                sort_order = :sort_order
            WHERE id = :id
              AND venue_id = :venue_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $id,
            ':venue_id' => (int) $data['venue_id'],
            ':name' => (string) $data['name'],
            ':sort_order' => (int) $data['sort_order'],
        ]);
    }

    public static function deleteCategoryIfEmpty(int $id, int $venueId): bool
    {
        $pdo = db();

        $countStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM menu_items
            WHERE category_id = :category_id
        ");
        $countStmt->execute([':category_id' => $id]);

        if ((int) $countStmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $pdo->prepare("
            DELETE FROM menu_categories
            WHERE id = :id
              AND venue_id = :venue_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $id,
            ':venue_id' => $venueId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function findItem(int $id): ?array
    {
        $pdo = db();

        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public static function createItem(array $data): int
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            INSERT INTO menu_items (
                venue_id,
                category_id,
                name,
                description,
                image_url,
                image_alt_text,
                price,
                sort_order,
                is_published
            ) VALUES (
                :venue_id,
                :category_id,
                :name,
                :description,
                :image_url,
                :image_alt_text,
                :price,
                :sort_order,
                :is_published
            )
        ");
        $stmt->execute([
            ':venue_id' => (int) $data['venue_id'],
            ':category_id' => $data['category_id'] !== null ? (int) $data['category_id'] : null,
            ':name' => (string) $data['name'],
            ':description' => $data['description'],
            ':image_url' => $data['image_url'],
            ':image_alt_text' => $data['image_alt_text'],
            ':price' => $data['price'],
            ':sort_order' => (int) $data['sort_order'],
            ':is_published' => !empty($data['is_published']) ? 1 : 0,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function updateItem(int $id, array $data): void
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            UPDATE menu_items
            SET category_id = :category_id,
                name = :name,
                description = :description,
                image_url = :image_url,
                image_alt_text = :image_alt_text,
                price = :price,
                sort_order = :sort_order,
                is_published = :is_published
            WHERE id = :id
              AND venue_id = :venue_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $id,
            ':venue_id' => (int) $data['venue_id'],
            ':category_id' => $data['category_id'] !== null ? (int) $data['category_id'] : null,
            ':name' => (string) $data['name'],
            ':description' => $data['description'],
            ':image_url' => $data['image_url'],
            ':image_alt_text' => $data['image_alt_text'],
            ':price' => $data['price'],
            ':sort_order' => (int) $data['sort_order'],
            ':is_published' => !empty($data['is_published']) ? 1 : 0,
        ]);
    }

    public static function setItemPublished(int $id, int $venueId, bool $published): void
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            UPDATE menu_items
            SET is_published = :is_published
            WHERE id = :id
              AND venue_id = :venue_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $id,
            ':venue_id' => $venueId,
            ':is_published' => $published ? 1 : 0,
        ]);
    }

    public static function deleteItem(int $id, int $venueId): bool
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            DELETE FROM menu_items
            WHERE id = :id
              AND venue_id = :venue_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $id,
            ':venue_id' => $venueId,
        ]);

        return $stmt->rowCount() > 0;
    }
    /**
     * All dietary tags, ordered for admin/public display.
     *
     * @return array<int, array{id:int, name:string, slug:string, sort_order:int}>
     */
    public static function dietaryTags(): array
    {
        $pdo = db();

        $sql = "
            SELECT id, name, slug, sort_order
            FROM dietary_tags
            ORDER BY sort_order ASC, name ASC
        ";

        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Admin: selected dietary tag ids for one menu item.
     *
     * @return array<int, int>
     */
    public static function dietaryTagIdsForItem(int $itemId): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT dietary_tag_id
            FROM menu_item_dietary_tags
            WHERE menu_item_id = :item_id
            ORDER BY dietary_tag_id ASC
        ");
        $stmt->execute([':item_id' => $itemId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Admin: allergen statuses for one menu item, keyed by allergen id.
     *
     * @return array<int, string>
     */
    public static function allergenStatusesForItem(int $itemId): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT allergen_id, status
            FROM menu_item_allergen_statuses
            WHERE menu_item_id = :item_id
        ");
        $stmt->execute([':item_id' => $itemId]);

        $statuses = [];
        foreach ($stmt->fetchAll() as $row) {
            $statuses[(int) $row['allergen_id']] = (string) $row['status'];
        }

        return $statuses;
    }

    /**
     * Admin: replace dietary tags for a menu item.
     *
     * @param array<int, int|string> $tagIds
     */
    public static function syncItemDietaryTags(int $itemId, array $tagIds): void
    {
        $pdo = db();

        $cleanIds = [];
        foreach ($tagIds as $id) {
            if (is_numeric($id) && (int) $id > 0) {
                $cleanIds[] = (int) $id;
            }
        }
        $cleanIds = array_values(array_unique($cleanIds));

        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare("
                DELETE FROM menu_item_dietary_tags
                WHERE menu_item_id = :item_id
            ");
            $delete->execute([':item_id' => $itemId]);

            if ($cleanIds !== []) {
                $insert = $pdo->prepare("
                    INSERT INTO menu_item_dietary_tags (menu_item_id, dietary_tag_id)
                    VALUES (:item_id, :tag_id)
                ");

                foreach ($cleanIds as $tagId) {
                    $insert->execute([
                        ':item_id' => $itemId,
                        ':tag_id'  => $tagId,
                    ]);
                }
            }

            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    /**
     * Admin: replace allergen statuses for a menu item.
     *
     * Unknown statuses are not stored. Missing rows are treated as unknown.
     *
     * @param array<int|string, string> $statusesByAllergenId
     */
    public static function syncItemAllergenStatuses(int $itemId, array $statusesByAllergenId): void
    {
        $allowed = [
            'contains',
            'does_not_contain',
            'may_contain',
            'cross_contact_risk',
            'unknown',
        ];

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare("
                DELETE FROM menu_item_allergen_statuses
                WHERE menu_item_id = :item_id
            ");
            $delete->execute([':item_id' => $itemId]);

            $insert = $pdo->prepare("
                INSERT INTO menu_item_allergen_statuses (menu_item_id, allergen_id, status)
                VALUES (:item_id, :allergen_id, :status)
            ");

            foreach ($statusesByAllergenId as $allergenId => $status) {
                if (!is_numeric($allergenId) || (int) $allergenId <= 0) {
                    continue;
                }

                $status = (string) $status;
                if (!in_array($status, $allowed, true) || $status === 'unknown') {
                    continue;
                }

                $insert->execute([
                    ':item_id'     => $itemId,
                    ':allergen_id' => (int) $allergenId,
                    ':status'      => $status,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }
    /**
     * Resolve an allergen slug to its id (or null if unknown).
     */
    private static function allergenIdBySlug(string $slug): ?int
    {
        $pdo = db();

        $sql = "SELECT id FROM allergens WHERE slug = :slug LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':slug' => $slug]);

        $row = $stmt->fetch();

        return $row !== false ? (int) $row['id'] : null;
    }

    /**
     * Fetch all dietary tags for a set of menu item ids.
     *
     * @param array<int, int> $itemIds
     *
     * @return array<int, array<int, array{id:int, name:string, slug:string}>>
     */
    private static function dietaryTagsForItems(array $itemIds): array
    {
        if ($itemIds === []) {
            return [];
        }

        $pdo = db();

        $placeholders = [];
        $params = [];
        foreach (array_values(array_unique($itemIds)) as $i => $id) {
            $key = ':id' . $i;
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $inList = implode(', ', $placeholders);

        $sql = "
            SELECT
                midt.menu_item_id,
                dt.id,
                dt.name,
                dt.slug
            FROM menu_item_dietary_tags midt
            INNER JOIN dietary_tags dt ON dt.id = midt.dietary_tag_id
            WHERE midt.menu_item_id IN ($inList)
            ORDER BY dt.sort_order ASC, dt.name ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $itemId = (int) $row['menu_item_id'];
            if (!isset($result[$itemId])) {
                $result[$itemId] = [];
            }

            $result[$itemId][] = [
                'id'   => (int) $row['id'],
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
            ];
        }

        return $result;
    }
    /**
     * Fetch all allergen statuses for a set of menu item ids.
     *
     * @param array<int, int> $itemIds
     *
     * @return array<int, array<string, string>> Map of item_id => [allergen_slug => status]
     */
    private static function allergenStatusesForItems(array $itemIds): array
    {
        if ($itemIds === []) {
            return [];
        }

        $pdo = db();

        // Safe IN() expansion with unique placeholders.
        $placeholders = [];
        $params = [];
        foreach (array_values(array_unique($itemIds)) as $i => $id) {
            $key = ':id' . $i;
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $inList = implode(', ', $placeholders);

        $sql = "
            SELECT
                mias.menu_item_id,
                a.slug AS allergen_slug,
                a.name AS allergen_name,
                mias.status
            FROM menu_item_allergen_statuses mias
            INNER JOIN allergens a ON a.id = mias.allergen_id
            WHERE mias.menu_item_id IN ($inList)
            ORDER BY a.name ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $itemId = (int) $row['menu_item_id'];
            if (!isset($result[$itemId])) {
                $result[$itemId] = [];
            }
            $result[$itemId][(string) $row['allergen_slug']] = (string) $row['status'];
        }

        return $result;
    }
}