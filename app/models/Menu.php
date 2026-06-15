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
 * unknown) — or no status row at all — are excluded.
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