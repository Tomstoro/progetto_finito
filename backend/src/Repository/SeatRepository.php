<?php

class SeatRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByRoomId(int $roomId, ?string $date = null, ?string $start = null, ?string $end = null): array
    {
        if ($date !== null && $start !== null && $end !== null) {
            $statement = $this->pdo->prepare(
                'SELECT t.id AS table_id, t.numero AS table_number, t.sedie AS seat_count
                 FROM `tables` t
                 WHERE t.room_id = :room_id
                   AND t.id NOT IN (
                       SELECT b.table_id
                       FROM bookings b
                       WHERE b.room_id = :room_id
                         AND b.data_prenotazione = :date
                         AND b.ora_inizio = :ora_inizio
                         AND b.ora_fine = :ora_fine
                         AND b.stato = :stato
                   )
                 ORDER BY t.numero'
            );
            $statement->execute([
                'room_id' => $roomId,
                'date' => $date,
                'ora_inizio' => $start,
                'ora_fine' => $end,
                'stato' => 'confermata',
            ]);

            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $statement = $this->pdo->prepare(
            'SELECT t.id AS table_id, t.numero AS table_number, t.sedie AS seat_count
             FROM `tables` t
             WHERE t.room_id = :room_id
             ORDER BY t.numero'
        );
        $statement->execute(['room_id' => $roomId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
