<?php
declare(strict_types=1);

/**
 * File: app/models/Rsvp.php
 * Purpose: Venue RSVP model for creating and reviewing RSVP requests.
 * Batch: B1 RSVP schema + backend foundation.
 */

final class Rsvp
{
    public const STATUSES = ['new', 'contacted', 'confirmed', 'cancelled'];

    /**
     * Insert a new RSVP request.
     *
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        $pdo = db();

        $stmt = $pdo->prepare(
            'INSERT INTO venue_rsvps
                (venue_id, name, phone, email, party_size, requested_date,
                 requested_time, notes, status, source_context, ip_address, user_agent)
             VALUES
                (:venue_id, :name, :phone, :email, :party_size, :requested_date,
                 :requested_time, :notes, :status, :source_context, :ip_address, :user_agent)'
        );

        $stmt->execute([
            ':venue_id'       => $data['venue_id'],
            ':name'           => $data['name'],
            ':phone'          => $data['phone'] ?? null,
            ':email'          => $data['email'] ?? null,
            ':party_size'     => $data['party_size'] ?? null,
            ':requested_date' => $data['requested_date'] ?? null,
            ':requested_time' => $data['requested_time'] ?? null,
            ':notes'          => $data['notes'] ?? null,
            ':status'         => $data['status'] ?? 'new',
            ':source_context' => $data['source_context'] ?? null,
            ':ip_address'     => $data['ip_address'] ?? null,
            ':user_agent'     => $data['user_agent'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Most recent RSVPs for a single venue.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function recentForVenue(int $venueId, int $limit = 20): array
    {
        $pdo = db();

        $stmt = $pdo->prepare('
            SELECT *
            FROM venue_rsvps
            WHERE venue_id = :venue_id
            ORDER BY created_at DESC
            LIMIT :limit
        ');

        $stmt->bindValue(':venue_id', $venueId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Most recent RSVPs across all venues, with venue name/slug attached.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function recent(int $limit = 50): array
    {
        $pdo = db();

        $stmt = $pdo->prepare('
            SELECT r.*, v.name AS venue_name, v.slug AS venue_slug
            FROM venue_rsvps r
            LEFT JOIN venues v ON v.id = r.venue_id
            ORDER BY r.created_at DESC
            LIMIT :limit
        ');

        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Find one RSVP by id.
     *
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $pdo = db();

        $stmt = $pdo->prepare('SELECT * FROM venue_rsvps WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid RSVP status: ' . $status);
        }

        $pdo = db();

        $stmt = $pdo->prepare('UPDATE venue_rsvps SET status = :status WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':status' => $status,
        ]);
    }
}