<?php

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['email']) || empty($input['password'])) {
            JsonResponse::error('Email e password sono obbligatorie', 400);
        }

        try {
            $data = $this->authService->login(trim((string) $input['email']), (string) $input['password']);
            JsonResponse::success($data);
        } catch (RuntimeException $e) {
            JsonResponse::error($e->getMessage(), 401);
        }
    }

    public function profile(): void
    {
        try {
            $data = $this->authService->getProfile();
            JsonResponse::success($data);
        } catch (RuntimeException $e) {
            JsonResponse::error($e->getMessage(), 401);
        }
    }

    public function changePassword(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['currentPassword']) || empty($input['newPassword'])) {
            JsonResponse::error('currentPassword e newPassword sono obbligatori', 400);
        }

        try {
            $this->authService->changePassword((string) $input['currentPassword'], (string) $input['newPassword']);
            JsonResponse::success(['message' => 'Password aggiornata con successo']);
        } catch (RuntimeException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }
}
