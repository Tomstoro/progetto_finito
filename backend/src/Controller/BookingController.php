<?php

class BookingController
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            JsonResponse::error('Payload non valido', 400);
        }

        try {
            $booking = $this->bookingService->createBooking($input);
            JsonResponse::success($booking, 201);
        } catch (BookingConflictException $e) {
            JsonResponse::error($e->getMessage(), 409);
        } catch (RuntimeException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }

    public function listMine(): void
    {
        JsonResponse::success($this->bookingService->getMyBookings());
    }

    public function cancel(int $bookingId): void
    {
        try {
            $result = $this->bookingService->cancelBooking($bookingId);
            JsonResponse::success($result);
        } catch (RuntimeException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }

    public function notImplemented(): void
    {
        JsonResponse::error('Da implementare', 501);
    }
}
