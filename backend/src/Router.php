<?php

class Router
{
    private AuthController $authController;
    private RoomController $roomController;
    private BookingController $bookingController;

    public function __construct(PDO $pdo)
    {
        $userModel = new UserModel($pdo);
        $authService = new AuthService($userModel);
        $roomModel = new RoomModel($pdo);
        $seatRepository = new SeatRepository($pdo);
        $timeSlotRepository = new TimeSlotRepository($pdo);
        $bookingRepository = new BookingRepository($pdo);
        $bookingModel = new BookingModel($pdo, $bookingRepository);

        $roomService = new RoomService($roomModel, $seatRepository, $timeSlotRepository);
        $bookingService = new BookingService($bookingModel, $bookingRepository, $authService);

        $this->authController = new AuthController($authService);
        $this->roomController = new RoomController($roomService);
        $this->bookingController = new BookingController($bookingService);
    }

    public static function createPdo(): PDO
    {
        $db = new Database(
            getenv('DB_HOST') ?: 'database',
            getenv('DB_NAME') ?: 'unipr_booking',
            getenv('DB_USER') ?: 'unipr_user',
            getenv('DB_PASS') ?: 'unipr_password'
        );

        return $db->getConnection();
    }

    public function dispatch(string $method, string $path): void
    {
        $query = [];
        parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '', $query);

        if ($method === 'POST' && $path === '/api/v1/auth/login') {
            $this->authController->login();
            return;
        }

        if ($method === 'GET' && $path === '/api/v1/auth/profile') {
            $this->authController->profile();
            return;
        }

        if ($method === 'POST' && $path === '/api/v1/auth/change-password') {
            $this->authController->changePassword();
            return;
        }

        if ($method === 'GET' && $path === '/api/v1/rooms') {
            $this->roomController->list();
            return;
        }

        if ($method === 'GET' && preg_match('#^/api/v1/rooms/(\d+)/seats$#', $path, $m)) {
            $this->roomController->seats((int) $m[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/api/v1/rooms/(\d+)/timeslots$#', $path, $m)) {
            $this->roomController->timeslots((int) $m[1]);
            return;
        }

        if ($method === 'POST' && $path === '/api/v1/bookings') {
            $this->bookingController->create();
            return;
        }

        if ($method === 'GET' && $path === '/api/v1/bookings' && isset($query['me'])) {
            $this->bookingController->listMine();
            return;
        }

        if ($method === 'DELETE' && preg_match('#^/api/v1/bookings/(\d+)$#', $path, $m)) {
            $this->bookingController->cancel((int) $m[1]);
            return;
        }

        if ($method === 'PUT' && preg_match('#^/api/v1/bookings/(\d+)$#', $path, $m)) {
            $this->bookingController->notImplemented();
            return;
        }

        JsonResponse::error('Endpoint non trovato', 404);
    }
}
