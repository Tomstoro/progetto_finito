<?php

class BookingService
{
    public function __construct(
        private BookingModel $bookingModel,
        private BookingRepository $bookingRepository,
        private AuthService $authService
    ) {}

    public function createBooking(array $input): array
    {
        $roomId = filter_var($input['roomId'] ?? null, FILTER_VALIDATE_INT);
        $tableId = filter_var($input['tableId'] ?? null, FILTER_VALIDATE_INT);
        $timeSlotId = filter_var($input['timeSlotId'] ?? null, FILTER_VALIDATE_INT);

        if ($roomId === false || $tableId === false || $timeSlotId === false) {
            throw new RuntimeException('roomId, tableId e timeSlotId sono obbligatori');
        }

        $userId = $this->authService->resolveUserIdFromRequest();

        return $this->bookingModel->createBooking($userId, $roomId, $tableId, $timeSlotId);
    }

    public function getMyBookings(): array
    {
        $userId = $this->authService->resolveUserIdFromRequest();

        return $this->bookingRepository->findByUserId($userId);
    }

    public function cancelBooking(int $bookingId): array
    {
        $userId = $this->authService->resolveUserIdFromRequest();

        if (!$this->bookingRepository->cancelBooking($userId, $bookingId)) {
            throw new RuntimeException('Prenotazione non trovata o già cancellata');
        }

        return ['id' => $bookingId, 'status' => 'cancellata'];
    }
}
