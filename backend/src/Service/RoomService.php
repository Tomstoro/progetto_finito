<?php

class RoomService
{
    public function __construct(
        private RoomModel $roomModel,
        private SeatRepository $seatRepository,
        private TimeSlotRepository $timeSlotRepository
    ) {}

    public function getAllRooms(): array
    {
        return $this->roomModel->findAllActive();
    }

    public function getSeatsWithMap(int $roomId, ?string $date = null, ?string $start = null, ?string $end = null): array
    {
        $room = $this->roomModel->findById($roomId);
        if ($room === null) {
            throw new RuntimeException('Aula non trovata');
        }

        return [
            'room' => $room,
            'tables' => $this->seatRepository->findByRoomId($roomId, $date, $start, $end),
        ];
    }

    public function getTimeSlots(int $roomId, string $date): array
    {
        $room = $this->roomModel->findById($roomId);
        if ($room === null) {
            throw new RuntimeException('Aula non trovata');
        }

        return $this->timeSlotRepository->findByRoomIdAndDate($roomId, $date);
    }
}
