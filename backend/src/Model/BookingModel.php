<?php

/**
 * Model prenotazioni — implementare transazione e concorrenza.
 *
 * TODO concorrenza:
 * - beginTransaction()
 * - SELECT ... FOR UPDATE sul posto
 * - INSERT booking
 * - UNIQUE(seat_id, timeslot_id) come ultima difesa contro overbooking
 */
class BookingConflictException extends RuntimeException {}

class BookingModel
{
    public function __construct(private PDO $pdo, private ?BookingRepository $bookingRepository = null) {}

    /**
     * @return array{id: int, stato: string, data_prenotazione: string, ora_inizio: string, ora_fine: string}
     */
    public function createBooking(int $userId, int $roomId, int $tableId, int $timeSlot): array
    {
        $slot = $this->findTimeSlot($roomId, $timeSlot);
        if ($slot === null) {
            throw new RuntimeException('Fascia oraria non valida');
        }

        $this->pdo->beginTransaction();
        try {
            $tableStatement = $this->pdo->prepare(
                'SELECT id, numero, sedie
                FROM `tables`
                WHERE id = :table_id AND room_id = :room_id
                LIMIT 1'
            );
            $tableStatement->execute(['table_id' => $tableId, 'room_id' => $roomId]);
            $table = $tableStatement->fetch(PDO::FETCH_ASSOC);

            if (!$table) {
                throw new RuntimeException('Posto non valido per l\'aula scelta');
            }

            $reservedSeats = (int) $table['sedie'];

            $lockStatement = $this->pdo->prepare(
                'SELECT id
                FROM bookings
                WHERE table_id = :table_id
                AND data_prenotazione = :data_prenotazione
                AND ora_inizio = :ora_inizio
                AND ora_fine = :ora_fine
                AND stato = :stato
                FOR UPDATE'
            );
            $lockStatement->execute([
                'table_id' => $tableId,
                'data_prenotazione' => $slot['data_prenotazione'],
                'ora_inizio' => $slot['ora_inizio'],
                'ora_fine' => $slot['ora_fine'],
                'stato' => 'confermata',
            ]);

            if ($lockStatement->fetch()) {
                throw new BookingConflictException('Posto già prenotato in questa fascia oraria');
            }

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO bookings (student_id, room_id, table_id, posti_richiesti, stato, data_prenotazione, ora_inizio, ora_fine)
                VALUES (:student_id, :room_id, :table_id, :posti_richiesti, :stato, :data_prenotazione, :ora_inizio, :ora_fine)'
            );
            $insertStatement->execute([
                'student_id' => $userId,
                'room_id' => $roomId,
                'table_id' => $tableId,
                'posti_richiesti' => $reservedSeats,
                'stato' => 'confermata',
                'data_prenotazione' => $slot['data_prenotazione'],
                'ora_inizio' => $slot['ora_inizio'],
                'ora_fine' => $slot['ora_fine'],
            ]);

            $bookingId = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();

            return [
                'id' => $bookingId,
                'stato' => 'confermata',
                'data_prenotazione' => $slot['data_prenotazione'],
                'ora_inizio' => $slot['ora_inizio'],
                'ora_fine' => $slot['ora_fine'],
            ];
        } catch (BookingConflictException $e) {
            $this->pdo->rollBack();
            throw $e;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Impossibile creare la prenotazione');
        }
    }

    private function findTimeSlot(int $roomId, int $timeSlot): ?array
    {
        $slots = (new TimeSlotRepository($this->pdo))->findByRoomId($roomId);
        foreach ($slots as $slot) {
            if ((int) $slot['id'] === $timeSlot) {
                return $slot;
            }
        }

        return null;
    }
}
