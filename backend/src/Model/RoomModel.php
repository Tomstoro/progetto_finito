<?php

class RoomModel
{
    public function __construct(private PDO $pdo) {}

    public function findAllActive(): array
    {
        $statement = $this->pdo->prepare('SELECT id, nome, edificio, piano, stato, capienza FROM rooms WHERE stato = :activeStatus ORDER BY nome ASC');
        $statement->execute(['activeStatus' => 'attiva']);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, nome, edificio, piano, stato, capienza FROM rooms WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $room = $statement->fetch(PDO::FETCH_ASSOC);
        return $room !== false ? $room : null;
    }
}
