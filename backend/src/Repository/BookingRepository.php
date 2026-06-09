<?php

class BookingRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT b.id,
                    r.nome AS room_name,
                    t.numero AS table_number,
                    b.stato,
                    b.data_prenotazione,
                    b.ora_inizio,
                    b.ora_fine
             FROM bookings b
             JOIN rooms r ON r.id = b.room_id
             JOIN `tables` t ON t.id = b.table_id
             WHERE b.student_id = :student_id
             ORDER BY b.data_prenotazione DESC, b.ora_inizio DESC'
        );
        $statement->execute(['student_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function cancelBooking(int $userId, int $bookingId): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE bookings
             SET stato = :stato
             WHERE id = :id
               AND student_id = :student_id
               AND stato = :current_stato'
        );

        $statement->execute([
            'stato' => 'cancellata',
            'id' => $bookingId,
            'student_id' => $userId,
            'current_stato' => 'confermata',
        ]);

        return $statement->rowCount() > 0;
    }
}
