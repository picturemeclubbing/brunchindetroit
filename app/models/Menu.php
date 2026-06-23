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
 * unknown) â€” or no status row at all â€” are excluded.
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
                'price'             => $item['price'] !== null ? (string) $item['price'] : null,
                'category_id'       => $categoryId,
                'sort_order'        => (int) $item['sort_order'],
                'allergen_statuses' => $statusesByItem[$itemId] ?? [],
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
                price,
                sort_order,
                is_published
            ) VALUES (
                :venue_id,
                :category_id,
                :name,
                :description,
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