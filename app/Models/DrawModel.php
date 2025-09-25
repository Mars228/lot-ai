<?php

// ==========================================
// app/Models/DrawModel.php - KOMPLETNIE POPRAWIONY dla PHP 8.4
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class DrawModel extends Model
{
    protected $table = 'draws';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'game_id', 'draw_number', 'draw_number_global', 'draw_date', 
        'draw_time', 'numbers_main', 'numbers_bonus', 'jackpot_amount', 'data_source'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Pobiera najnowsze losowanie dla gry
     */
    public function getLatestDrawForGame(int $gameId): ?array
    {
        try {
            $draw = $this->where('game_id', $gameId)
                ->orderBy('draw_number', 'DESC')
                ->first();

            if ($draw) {
                $draw['numbers_main'] = !empty($draw['numbers_main']) ? json_decode($draw['numbers_main'], true) : [];
                $draw['numbers_bonus'] = !empty($draw['numbers_bonus']) ? json_decode($draw['numbers_bonus'], true) : null;
            }

            return $draw;
        } catch (\Throwable $e) {
            log_message('error', 'Błąd w getLatestDrawForGame: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Pobiera losowania w zakresie dat
     */
    public function getDrawsByDateRange(int $gameId, string $startDate, string $endDate): array
    {
        try {
            $draws = $this->where('game_id', $gameId)
                ->where('draw_date >=', $startDate)
                ->where('draw_date <=', $endDate)
                ->orderBy('draw_date', 'DESC')
                ->findAll();

            foreach ($draws as &$draw) {
                $draw['numbers_main'] = !empty($draw['numbers_main']) ? json_decode($draw['numbers_main'], true) : [];
                $draw['numbers_bonus'] = !empty($draw['numbers_bonus']) ? json_decode($draw['numbers_bonus'], true) : null;
            }

            return $draws;
        } catch (\Throwable $e) {
            log_message('error', 'Błąd w getDrawsByDateRange: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Pobiera losowania dla ostatniego miesiąca
     */
    public function getLastMonthDraws(int $gameId): array
    {
        $startDate = date('Y-m-d', strtotime('-1 month'));
        return $this->getDrawsByDateRange($gameId, $startDate, date('Y-m-d'));
    }

    /**
     * Pobiera konkretne losowanie po numerze
     */
    public function getDrawByNumber(int $gameId, int $drawNumber): ?array
    {
        try {
            $draw = $this->where('game_id', $gameId)
                ->where('draw_number', $drawNumber)
                ->first();

            if ($draw) {
                $draw['numbers_main'] = !empty($draw['numbers_main']) ? json_decode($draw['numbers_main'], true) : [];
                $draw['numbers_bonus'] = !empty($draw['numbers_bonus']) ? json_decode($draw['numbers_bonus'], true) : null;
            }

            return $draw;
        } catch (\Throwable $e) {
            log_message('error', 'Błąd w getDrawByNumber: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sprawdza czy losowanie istnieje
     */
    public function drawExists(int $gameId, int $drawNumber): bool
    {
        try {
            return $this->where('game_id', $gameId)
                ->where('draw_number', $drawNumber)
                ->countAllResults() > 0;
        } catch (\Throwable $e) {
            log_message('error', 'Błąd w drawExists: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pobiera brakujące numery losowań w zakresie
     */
    public function getMissingDrawNumbers(int $gameId, int $startNumber, int $endNumber): array
    {
        try {
            $builder = $this->builder();
            $existing = $builder->select('draw_number')
                ->where('game_id', $gameId)
                ->where('draw_number >=', $startNumber)
                ->where('draw_number <=', $endNumber)
                ->get()
                ->getResultArray();

            $existingNumbers = array_column($existing, 'draw_number');
            $all = range($startNumber, $endNumber);
            
            return array_diff($all, $existingNumbers);
        } catch (\Throwable $e) {
            log_message('error', 'Błąd w getMissingDrawNumbers: ' . $e->getMessage());
            return [];
        }
    }


    /**
 * Import danych z CSV - POPRAWIONA WERSJA dla PHP 8.4+
 */
/**
 * Import danych z CSV - POPRAWIONA WERSJA z wykrywaniem nagłówków
 */
public function importFromCsv(int $gameId, string $csvFilePath): array
{
    if (!file_exists($csvFilePath)) {
        throw new \Exception("Plik CSV nie istnieje: {$csvFilePath}");
    }

    $imported = 0;
    $updated = 0;
    $errors = [];

    $handle = fopen($csvFilePath, 'r');
    if (!$handle) {
        throw new \Exception("Nie można otworzyć pliku CSV");
    }

    $db = \Config\Database::connect();

    // Pierwszy wiersz - sprawdź czy to nagłówek czy dane
    $firstRow = fgetcsv($handle, 0, ';', '"', '');
    
    if ($firstRow === false) {
        fclose($handle);
        throw new \Exception("Plik CSV jest pusty");
    }

    $isHeader = false;
    
    // Sprawdź czy pierwszy wiersz to nagłówek
    if (!empty($firstRow[0])) {
        $firstColumn = trim($firstRow[0]);
        
        // Jeśli pierwsza kolumna nie jest liczbą, to prawdopodobnie nagłówek
        if (!is_numeric($firstColumn) || 
            stripos($firstColumn, 'numer') !== false || 
            stripos($firstColumn, 'losowanie') !== false) {
            $isHeader = true;
        }
    }

    // Jeśli to nie nagłówek, cofnij wskaźnik pliku
    if (!$isHeader) {
        rewind($handle);
        $firstRow = fgetcsv($handle, 0, ';', '"', ''); // Przeczytaj ponownie pierwszy wiersz
    }

    // Jeśli to był nagłówek, pierwszą linię już przeczytaliśmy i pomijamy
    // Jeśli to nie był nagłówek, mamy pierwszą linię danych w $firstRow

    $rowsToProcess = [];
    
    // Dodaj pierwszy wiersz do przetworzenia (jeśli to nie nagłówek)
    if (!$isHeader) {
        $rowsToProcess[] = $firstRow;
    }

    // Przeczytaj resztę pliku
    while (($data = fgetcsv($handle, 0, ';', '"', '')) !== false) {
        $rowsToProcess[] = $data;
    }

    // Przetwórz wszystkie wiersze danych
    foreach ($rowsToProcess as $lineNumber => $data) {
        try {
            // DEBUG - usuń po naprawie
            log_message('error', 'CSV ROW ' . ($lineNumber + 1) . ': ' . print_r($data, true));

            // Sprawdź strukturę - oczekujemy 4 kolumn dla średników
            if (count($data) < 4) {
                $errors[] = "Wiersz " . ($lineNumber + 1) . ": Nieprawidłowa struktura wiersza: " . implode(';', $data);
                continue;
            }

            $drawNumber = (int)trim($data[0]);
            if ($drawNumber <= 0) {
                $errors[] = "Wiersz " . ($lineNumber + 1) . ": Nieprawidłowy numer losowania: {$data[0]}";
                continue;
            }

            $drawDate = trim($data[1]);
            $drawTime = !empty($data[2]) ? trim($data[2]) : null;
            $numbersMain = $this->parseNumbers(trim($data[3]));
            $numbersBonus = isset($data[4]) && !empty($data[4]) ? $this->parseNumbers(trim($data[4])) : null;

            // DEBUG - usuń po naprawie
            log_message('error', 'PARSED - Number: ' . $drawNumber . ', Date: ' . $drawDate . ', Numbers: ' . print_r($numbersMain, true));

            if (empty($numbersMain)) {
                $errors[] = "Wiersz " . ($lineNumber + 1) . ": Brak liczb głównych dla losowania: {$drawNumber}";
                continue;
            }

            // Walidacja daty
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $drawDate)) {
                $errors[] = "Wiersz " . ($lineNumber + 1) . ": Nieprawidłowy format daty '{$drawDate}' dla losowania {$drawNumber}. Oczekiwany: YYYY-MM-DD";
                continue;
            }

            $drawData = [
                'game_id' => $gameId,
                'draw_number' => $drawNumber,
                'draw_date' => $drawDate,
                'draw_time' => $drawTime,
                'numbers_main' => json_encode($numbersMain),
                'numbers_bonus' => $numbersBonus ? json_encode($numbersBonus) : null,
                'data_source' => 'csv'
            ];

            // Sprawdzamy czy losowanie już istnieje
            if ($this->drawExists($gameId, $drawNumber)) {
                $builder = $db->table($this->table);
                $builder->where('game_id', $gameId)
                       ->where('draw_number', $drawNumber)
                       ->update($drawData);
                $updated++;
            } else {
                $this->insert($drawData);
                $imported++;
            }

        } catch (\Throwable $e) {
            $errors[] = "Wiersz " . ($lineNumber + 1) . ": Błąd - " . $e->getMessage();
            log_message('error', "Import CSV error: " . $e->getMessage());
        }
    }

    fclose($handle);

    // DEBUG - usuń po naprawie
    log_message('error', "IMPORT SUMMARY - Header detected: " . ($isHeader ? 'YES' : 'NO') . ", Imported: {$imported}, Updated: {$updated}, Errors: " . count($errors));

    return [
        'imported' => $imported,
        'updated' => $updated,
        'errors' => $errors
    ];
}



    /**
 * Parsuje liczby z formatu CSV - POPRAWIONA WERSJA
 */
private function parseNumbers(string $numbersString): array
{
    // Usuń wszystkie cudzysłowy, apostrofy i białe znaki
    $numbersString = str_replace(['"', "'", ' ', '\t'], '', $numbersString);
    // Dzieli po przecinkach i filtruje liczby
    // Sortuje i usuwa duplikaty


    $numbersString = trim($numbersString);
    
    if (empty($numbersString)) {
        return [];
    }
    
    // Podziel po przecinkach
    $numbers = explode(',', $numbersString);
    
    $validNumbers = [];
    foreach ($numbers as $number) {
        $number = trim($number);
        if (is_numeric($number) && $number !== '') {
            $validNumbers[] = (int)$number;
        }
    }

    // Usuń duplikaty i posortuj
    $validNumbers = array_unique($validNumbers);
    sort($validNumbers);

    return array_values($validNumbers);
}

    /**
     * Pobiera statystyki częstości liczb - POPRAWIONA WERSJA DLA PHP 8.4
     */
    public function getNumberFrequency(int $gameId, ?int $lastNDraws = null, string $rangeType = 'main'): array
    {
        try {
            $column = $rangeType === 'main' ? 'numbers_main' : 'numbers_bonus';
            
            $builder = $this->builder();
            $builder->select($column)
                ->where('game_id', $gameId)
                ->where("{$column} IS NOT NULL")
                ->where("{$column} !=", '')
                ->orderBy('draw_number', 'DESC');

            if ($lastNDraws !== null && $lastNDraws > 0) {
                $builder->limit($lastNDraws);
            }

            $draws = $builder->get()->getResultArray();
            $frequency = [];

            foreach ($draws as $draw) {
                $numbers = !empty($draw[$column]) ? json_decode($draw[$column], true) : [];
                if (!is_array($numbers)) continue;

                foreach ($numbers as $number) {
                    if (is_numeric($number)) {
                        $number = (int)$number;
                        $frequency[$number] = ($frequency[$number] ?? 0) + 1;
                    }
                }
            }

            arsort($frequency);
            return $frequency;

        } catch (\Throwable $e) {
            log_message('error', 'Błąd w getNumberFrequency: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Pobiera wszystkie losowania dla gry (z paginacją)
     */
    public function getDrawsForGame(int $gameId, int $page = 1, int $perPage = 50): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $draws = $this->where('game_id', $gameId)
                ->orderBy('draw_number', 'DESC')
                ->findAll($perPage, $offset);

            foreach ($draws as &$draw) {
                $draw['numbers_main'] = !empty($draw['numbers_main']) ? json_decode($draw['numbers_main'], true) : [];
                $draw['numbers_bonus'] = !empty($draw['numbers_bonus']) ? json_decode($draw['numbers_bonus'], true) : null;
            }

            return $draws;
        } catch (\Throwable $e) {
            log_message('error', 'Błąd w getDrawsForGame: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Pobiera łączną liczbę losowań dla gry
     */
    public function getTotalDrawsForGame(int $gameId): int
    {
        try {
            return $this->where('game_id', $gameId)->countAllResults();
        } catch (\Throwable $e) {
            log_message('error', 'Błąd w getTotalDrawsForGame: ' . $e->getMessage());
            return 0;
        }
    }


/**
 * Pobiera zakres numerów losowań dla gry
 */
public function getDrawNumberRange(int $gameId): array
{
    try {
        $builder = $this->builder();
        $result = $builder->select('MIN(draw_number) as min_draw, MAX(draw_number) as max_draw')
            ->where('game_id', $gameId)
            ->get()
            ->getRowArray();

        return [
            'min' => $result['min_draw'] ?? 0,
            'max' => $result['max_draw'] ?? 0
        ];
    } catch (\Throwable $e) {
        log_message('error', 'Błąd w getDrawNumberRange: ' . $e->getMessage());
        return ['min' => 0, 'max' => 0];
    }
}



/**
 * Usuwa wszystkie losowania dla gry
 */
public function deleteAllForGame(int $gameId): bool
{
    try {
        return $this->where('game_id', $gameId)->delete();
    } catch (\Throwable $e) {
        log_message('error', 'Błąd w deleteAllForGame: ' . $e->getMessage());
        return false;
    }
}

/**
 * Pobiera ostatnie losowanie na podstawie daty
 */
public function getLatestDrawByDate(int $gameId): ?array
{
    try {
        $draw = $this->where('game_id', $gameId)
            ->orderBy('draw_date', 'DESC')
            ->orderBy('draw_time', 'DESC')
            ->first();

        if ($draw) {
            $draw['numbers_main'] = !empty($draw['numbers_main']) ? json_decode($draw['numbers_main'], true) : [];
            $draw['numbers_bonus'] = !empty($draw['numbers_bonus']) ? json_decode($draw['numbers_bonus'], true) : null;
        }

        return $draw;
    } catch (\Throwable $e) {
        log_message('error', 'Błąd w getLatestDrawByDate: ' . $e->getMessage());
        return null;
    }
}

/**
 * Sprawdza czy są jakiekolwiek losowania dla gry
 */
public function hasAnyDraws(int $gameId): bool
{
    try {
        return $this->where('game_id', $gameId)->countAllResults() > 0;
    } catch (\Throwable $e) {
        log_message('error', 'Błąd w hasAnyDraws: ' . $e->getMessage());
        return false;
    }
}


    
}