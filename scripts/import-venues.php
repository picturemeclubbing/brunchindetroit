<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only." . PHP_EOL);
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';
require APP_ROOT . '/models/Venue.php';

$options = getopt('', ['file:', 'commit', 'update', 'create-neighborhoods']);

$file = (string) ($options['file'] ?? 'data/venues_import.csv');
$root = dirname(__DIR__);
$csvPath = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($file, '/\\'));

$commit = array_key_exists('commit', $options);
$update = array_key_exists('update', $options);
$createNeighborhoods = array_key_exists('create-neighborhoods', $options);

if (!is_file($csvPath)) {
    fwrite(STDERR, "CSV file not found: {$file}" . PHP_EOL);
    exit(1);
}

function import_slugify(string $value): string
{
    $value = trim(strtolower($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'untitled';
}

function import_nullable(mixed $value): ?string
{
    $value = trim((string) $value);
    return $value === '' ? null : $value;
}

function import_bool(mixed $value): int
{
    $value = strtolower(trim((string) $value));
    return in_array($value, ['1', 'true', 'yes', 'y', 'on'], true) ? 1 : 0;
}

function import_int(mixed $value): int
{
    $value = trim((string) $value);
    return is_numeric($value) ? (int) $value : 0;
}

function import_venue_id(PDO $pdo, string $slug): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM venues WHERE slug = :slug LIMIT 1');
    $stmt->execute([':slug' => $slug]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : null;
}

function import_neighborhood_id(PDO $pdo, string $name): ?int
{
    $name = trim($name);
    if ($name === '') {
        return null;
    }

    $slug = import_slugify($name);

    $stmt = $pdo->prepare('
        SELECT id
        FROM neighborhoods
        WHERE slug = :slug OR LOWER(name) = LOWER(:name)
        ORDER BY is_active DESC, sort_order ASC, name ASC
        LIMIT 1
    ');
    $stmt->execute([':slug' => $slug, ':name' => $name]);

    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : null;
}

function import_create_neighborhood(PDO $pdo, string $name): int
{
    $stmt = $pdo->prepare('
        INSERT INTO neighborhoods (name, slug, sort_order, is_active)
        VALUES (:name, :slug, 100, 1)
    ');
    $stmt->execute([
        ':name' => trim($name),
        ':slug' => import_slugify($name),
    ]);

    return (int) $pdo->lastInsertId();
}

$handle = fopen($csvPath, 'rb');
if ($handle === false) {
    fwrite(STDERR, "Could not open CSV file: {$file}" . PHP_EOL);
    exit(1);
}

$headers = fgetcsv($handle);
if ($headers === false) {
    fwrite(STDERR, "CSV file is empty: {$file}" . PHP_EOL);
    exit(1);
}

$headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);

$required = [
    'name',
    'slug',
    'description',
    'address_line1',
    'address_line2',
    'city',
    'state',
    'zip',
    'phone',
    'website_url',
    'instagram_url',
    'facebook_url',
    'neighborhood',
    'price_range',
    'brunch_hours_note',
    'main_image_path',
    'is_published',
    'is_featured',
    'featured_sort',
];

$missing = array_diff($required, $headers);
if ($missing !== []) {
    fwrite(STDERR, "Missing CSV columns: " . implode(', ', $missing) . PHP_EOL);
    exit(1);
}

$pdo = db();

$created = 0;
$updated = 0;
$skipped = 0;
$errors = 0;
$rowNumber = 1;

echo ($commit ? "MODE: COMMIT" : "MODE: DRY RUN") . PHP_EOL;
echo "FILE: {$file}" . PHP_EOL;
echo "UPDATE EXISTING: " . ($update ? "yes" : "no") . PHP_EOL;
echo "CREATE NEIGHBORHOODS: " . ($createNeighborhoods ? "yes" : "no") . PHP_EOL;
echo str_repeat('-', 72) . PHP_EOL;

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;

    if ($row === [null] || count(array_filter($row, static fn ($v): bool => trim((string) $v) !== '')) === 0) {
        continue;
    }

    $record = array_combine($headers, array_pad($row, count($headers), ''));
    if ($record === false) {
        $errors++;
        echo "Row {$rowNumber}: ERROR could not map columns" . PHP_EOL;
        continue;
    }

    $name = trim((string) ($record['name'] ?? ''));
    $slug = trim((string) ($record['slug'] ?? ''));

    if ($name === '' || $slug === '') {
        $errors++;
        echo "Row {$rowNumber}: ERROR missing name or slug" . PHP_EOL;
        continue;
    }

    $neighborhoodName = trim((string) ($record['neighborhood'] ?? ''));
    $neighborhoodId = import_neighborhood_id($pdo, $neighborhoodName);

    if ($neighborhoodName !== '' && $neighborhoodId === null) {
        if (!$createNeighborhoods) {
            $errors++;
            echo "Row {$rowNumber}: ERROR missing neighborhood {$neighborhoodName}" . PHP_EOL;
            continue;
        }

        if ($commit) {
            $neighborhoodId = import_create_neighborhood($pdo, $neighborhoodName);
            echo "Row {$rowNumber}: CREATED neighborhood {$neighborhoodName}" . PHP_EOL;
        } else {
            echo "Row {$rowNumber}: WOULD CREATE neighborhood {$neighborhoodName}" . PHP_EOL;
        }
    }

    $priceRange = import_nullable($record['price_range'] ?? null);
    if ($priceRange !== null && !in_array($priceRange, ['$', '$$', '$$$', '$$$$'], true)) {
        $errors++;
        echo "Row {$rowNumber}: ERROR invalid price_range {$priceRange}" . PHP_EOL;
        continue;
    }

    $data = [
        'slug' => $slug,
        'name' => $name,
        'description' => import_nullable($record['description'] ?? null),
        'address_line1' => import_nullable($record['address_line1'] ?? null),
        'address_line2' => import_nullable($record['address_line2'] ?? null),
        'city' => import_nullable($record['city'] ?? null),
        'state' => import_nullable($record['state'] ?? null),
        'zip' => import_nullable($record['zip'] ?? null),
        'phone' => import_nullable($record['phone'] ?? null),
        'website_url' => import_nullable($record['website_url'] ?? null),
        'instagram_url' => import_nullable($record['instagram_url'] ?? null),
        'facebook_url' => import_nullable($record['facebook_url'] ?? null),
        'neighborhood_id' => $neighborhoodId,
        'price_range' => $priceRange,
        'brunch_hours_note' => import_nullable($record['brunch_hours_note'] ?? null),
        'main_image_path' => import_nullable($record['main_image_path'] ?? null),
        'is_published' => import_bool($record['is_published'] ?? 0),
        'is_featured' => import_bool($record['is_featured'] ?? 0),
        'featured_sort' => import_int($record['featured_sort'] ?? 0),
    ];

    $existingId = import_venue_id($pdo, $slug);

    if ($existingId !== null) {
        if (!$update) {
            $skipped++;
            echo "Row {$rowNumber}: SKIP existing venue {$slug}" . PHP_EOL;
            continue;
        }

        if ($commit) {
            Venue::update($existingId, $data);
            echo "Row {$rowNumber}: UPDATED {$slug}" . PHP_EOL;
        } else {
            echo "Row {$rowNumber}: WOULD UPDATE {$slug}" . PHP_EOL;
        }

        $updated++;
        continue;
    }

    if ($commit) {
        Venue::create($data);
        echo "Row {$rowNumber}: CREATED {$slug}" . PHP_EOL;
    } else {
        echo "Row {$rowNumber}: WOULD CREATE {$slug}" . PHP_EOL;
    }

    $created++;
}

fclose($handle);

echo str_repeat('-', 72) . PHP_EOL;
echo "Created: {$created}" . PHP_EOL;
echo "Updated: {$updated}" . PHP_EOL;
echo "Skipped: {$skipped}" . PHP_EOL;
echo "Errors:  {$errors}" . PHP_EOL;

exit($errors > 0 ? 1 : 0);
