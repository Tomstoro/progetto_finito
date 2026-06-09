<?php

class UserModel
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, email, password_hash, matricola, nome, cognome FROM students WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user !== false ? $user : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, email, password_hash, matricola, nome, cognome FROM students WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user !== false ? $user : null;
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $statement = $this->pdo->prepare('UPDATE students SET password_hash = :password_hash WHERE id = :id');
        $statement->execute(['password_hash' => $passwordHash, 'id' => $id]);

        return $statement->rowCount() > 0;
    }
}
