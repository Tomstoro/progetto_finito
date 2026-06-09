<?php 
    $pdo = Router::createPdo();
    $router = new Router($pdo);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $router->dispatch($method, $path);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIPR Aula Studio Booking</title>
    <script src="js/apiClient.js"></script>
    <script>
        if (ApiClient.getToken()) {
            window.location.replace('rooms.html');
        } else {
            window.location.replace('login.html');
        }
    </script>
</head>
<body>
    <p class="text-center mt-5 text-muted">Reindirizzamento...</p>
</body>
</html>
