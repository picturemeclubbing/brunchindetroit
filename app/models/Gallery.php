<?php

declare(strict_types=1);

/**
 * Read-only Gallery model (Phase 4B).
 *
 * Provides published galleries for the public Gallery index. Galleries link
 * directly to an external gallery_url (SmugMug, etc.); this model does NOT
 * load local images or query gallery_images.
 *
 * Uses PDO through db() and prepared statements. All queries LEFT JOIN
 * venues and neighborhoods so a gallery with no venue still renders.
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
     * @param int|null    $month    Optional event_date month filter (1–12).
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
                g.location_label LIKE :location
                OR v.name LIKE :location
                OR n.name LIKE :location
            )";
            $params[':location'] = '%' . $location . '%';
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
}