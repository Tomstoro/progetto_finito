<?php

class RoomController
{
    public function __construct(private RoomService $roomService) {}

    public function list(): void
    {
        JsonResponse::success($this->roomService->getAllRooms());
    }

    public function seats(int $roomId): void
    {
        $params = [];
        parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '', $params);
        $date = $params['date'] ?? null;
        $start = $params['start'] ?? null;
        $end = $params['end'] ?? null;

        try {
            JsonResponse::success($this->roomService->getSeatsWithMap($roomId, $date, $start, $end));
        } catch (RuntimeException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }

    public function timeslots(int $roomId): void
    {
        $params = [];
        parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '', $params);
        $date = $params['date'] ?? null;

        if (!$date) {
            JsonResponse::error('La data è obbligatoria', 400);
            return;
        }

        try {
            JsonResponse::success($this->roomService->getTimeSlots($roomId, $date));
        } catch (RuntimeException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }
}
