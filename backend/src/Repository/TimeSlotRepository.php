<?php

class TimeSlotRepository
{
    private static array $holidays = [
        '01-01', '06-01', '25-04', '01-05', '02-06', '15-08', '01-11', '08-12', '25-12', '26-12',
    ];

    public function __construct(private PDO $pdo) {}

    public function findByRoomId(int $roomId): array
    {
        $today = new DateTimeImmutable('today');
        $endDate = $today->modify('+1 month');
        $timeRanges = [
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['11:00:00', '12:00:00'],
            ['12:00:00', '13:00:00'],
            ['14:00:00', '15:00:00'],
            ['15:00:00', '16:00:00'],
            ['16:00:00', '17:00:00'],
            ['17:00:00', '18:00:00'],
            ['18:00:00', '19:00:00'],
        ];

        $slots = [];
        for ($date = $today; $date <= $endDate; $date = $date->modify('+1 day')) {
            if ($this->isHoliday($date)) {
                continue;
            }

            foreach ($timeRanges as [$start, $end]) {
                if ($date == $today && (new DateTimeImmutable("{$date->format('Y-m-d')} {$end}")) <= new DateTimeImmutable()) {
                    continue;
                }

                $slots[] = $this->buildSlot($roomId, $date, $start, $end);
            }
        }

        return $slots;
    }

    public function findByRoomIdAndDate(int $roomId, string $date): array
    {
        $slotDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$slotDate) {
            throw new RuntimeException('Data non valida');
        }

        $this->ensureDateInRange($slotDate);
        if ($this->isHoliday($slotDate)) {
            throw new RuntimeException('La data selezionata è un giorno festivo');
        }

        $timeRanges = [
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['11:00:00', '12:00:00'],
            ['12:00:00', '13:00:00'],
            ['14:00:00', '15:00:00'],
            ['15:00:00', '16:00:00'],
            ['16:00:00', '17:00:00'],
            ['17:00:00', '18:00:00'],
            ['18:00:00', '19:00:00'],
        ];

        $slots = [];
        foreach ($timeRanges as [$start, $end]) {
            if ($slotDate == new DateTimeImmutable('today') && (new DateTimeImmutable("{$date} {$end}")) <= new DateTimeImmutable()) {
                continue;
            }
            $slots[] = $this->buildSlot($roomId, $slotDate, $start, $end);
        }

        return $slots;
    }

    private function buildSlot(int $roomId, DateTimeImmutable $slotDate, string $start, string $end): array
    {
        return [
            'id' => abs(crc32("{$roomId}|{$slotDate->format('Y-m-d')}|{$start}|{$end}")),
            'label' => sprintf('%s %s-%s', $slotDate->format('d/m'), substr($start, 0, 5), substr($end, 0, 5)),
            'data_prenotazione' => $slotDate->format('Y-m-d'),
            'ora_inizio' => $start,
            'ora_fine' => $end,
        ];
    }

    private function ensureDateInRange(DateTimeImmutable $date): void
    {
        $today = new DateTimeImmutable('today');
        $endDate = $today->modify('+1 month');

        if ($date < $today || $date > $endDate) {
            throw new RuntimeException('La data deve essere entro un mese da oggi');
        }
    }

    private function isHoliday(DateTimeImmutable $date): bool
    {
        return in_array($date->format('m-d'), self::$holidays, true);
    }
}
