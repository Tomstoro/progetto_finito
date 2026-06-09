<?php

/**
 * Front Controller — scheletro app-base (contratto swagger.yml).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/src/Http/JsonResponse.php';
require_once __DIR__ . '/src/Repository/RoomRepository.php';
require_once __DIR__ . '/src/Repository/SeatRepository.php';
require_once __DIR__ . '/src/Repository/TimeSlotRepository.php';
require_once __DIR__ . '/src/Repository/BookingRepository.php';
require_once __DIR__ . '/src/Model/BookingModel.php';
require_once __DIR__ . '/src/Model/UserModel.php';
require_once __DIR__ . '/src/Model/RoomModel.php';
require_once __DIR__ . '/src/Service/AuthService.php';
require_once __DIR__ . '/src/Service/RoomService.php';
require_once __DIR__ . '/src/Service/BookingService.php';
require_once __DIR__ . '/src/Controller/AuthController.php';
require_once __DIR__ . '/src/Controller/RoomController.php';
require_once __DIR__ . '/src/Controller/BookingController.php';
require_once __DIR__ . '/src/Router.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rtrim($uri, '/') ?: '/';

try {
    $router = new Router(Router::createPdo());
    $router->dispatch($method, $path);
} catch (Throwable $e) {
    JsonResponse::error($e->getMessage(), 500);
}
