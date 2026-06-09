<?php

class AuthService
{
    private string $secret;

    public function __construct(private UserModel $userModel)
    {
        $this->secret = getenv('JWT_SECRET') ?: 'unipr_jwt_secret_key_2026';
    }

    public function login(string $email, string $password): array
    {
        $student = $this->userModel->findByEmail($email);

        if (!$student) {
            throw new RuntimeException('Credenziali non valide');
        }

        $passwordHash = $student['password_hash'];
        if (str_starts_with($passwordHash, '$2y$') || str_starts_with($passwordHash, '$argon2')) {
            $validPassword = password_verify($password, $passwordHash);
        } else {
            $validPassword = hash_equals($passwordHash, $password);
        }

        if (!$validPassword) {
            throw new RuntimeException('Credenziali non valide');
        }

        $payload = [
            'sub' => (int) $student['id'],
            'email' => $student['email'],
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        return [
            'token' => $this->createJwt($payload),
            'user' => [
                'id' => (int) $student['id'],
                'email' => $student['email'],
            ],
        ];
    }

    public function getProfile(): array
    {
        $userId = $this->resolveUserIdFromRequest();
        $student = $this->findStudentById($userId);

        if (!$student) {
            throw new RuntimeException('Utente non trovato');
        }

        return [
            'id' => (int) $student['id'],
            'matricola' => $student['matricola'],
            'nome' => $student['nome'],
            'cognome' => $student['cognome'],
            'email' => $student['email'],
        ];
    }

    public function changePassword(string $currentPassword, string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new RuntimeException('La nuova password deve contenere almeno 8 caratteri');
        }

        $userId = $this->resolveUserIdFromRequest();
        $student = $this->findStudentById($userId);

        if (!$student) {
            throw new RuntimeException('Utente non trovato');
        }

        $passwordHash = $student['password_hash'];
        if (str_starts_with($passwordHash, '$2y$') || str_starts_with($passwordHash, '$argon2')) {
            $validPassword = password_verify($currentPassword, $passwordHash);
        } else {
            $validPassword = hash_equals($passwordHash, $currentPassword);
        }

        if (!$validPassword) {
            throw new RuntimeException('Password attuale non corretta');
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!$this->userModel->updatePassword($userId, $newHash)) {
            throw new RuntimeException('Impossibile aggiornare la password');
        }
    }

    private function findStudentById(int $id): ?array
    {
        return $this->userModel->findById($id);
    }

    public function resolveUserIdFromRequest(): int
    {
        $authorizationHeader = $this->getAuthorizationHeader();
        if ($authorizationHeader === null) {
            throw new RuntimeException('Autenticazione richiesta');
        }

        if (!preg_match('/^Bearer\s+(?<token>.+)$/i', $authorizationHeader, $matches)) {
            throw new RuntimeException('Header Authorization non valido');
        }

        $token = $matches['token'];
        $payload = $this->validateJwt($token);

        if (!isset($payload['sub']) || !is_int($payload['sub']) && !ctype_digit((string) $payload['sub'])) {
            throw new RuntimeException('JWT non valido');
        }

        return (int) $payload['sub'];
    }

    private function getAuthorizationHeader(): ?string
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                return trim($headers['Authorization']);
            }
            if (isset($headers['authorization'])) {
                return trim($headers['authorization']);
            }
        }

        return null;
    }

    private function createJwt(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
        ];

        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $this->secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function validateJwt(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('JWT non valido');
        }

        [$headerSegment, $payloadSegment, $signatureSegment] = $parts;
        $header = json_decode($this->base64UrlDecode($headerSegment), true);
        $payload = json_decode($this->base64UrlDecode($payloadSegment), true);

        if (!is_array($header) || !is_array($payload)) {
            throw new RuntimeException('JWT non valido');
        }

        if (($header['alg'] ?? '') !== 'HS256') {
            throw new RuntimeException('Algoritmo JWT non supportato');
        }

        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', "$headerSegment.$payloadSegment", $this->secret, true));
        if (!hash_equals($expectedSignature, $signatureSegment)) {
            throw new RuntimeException('Firma JWT non valida');
        }

        $now = time();
        if (isset($payload['exp']) && $now >= (int) $payload['exp']) {
            throw new RuntimeException('JWT scaduto');
        }

        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $padding = 4 - (strlen($data) % 4);
        if ($padding < 4) {
            $data .= str_repeat('=', $padding);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
