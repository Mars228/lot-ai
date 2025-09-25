<?php

// ==========================================
// app/Helpers/lottery_helper.php - PROSTY HELPER
// ==========================================

if (!function_exists('format_lottery_numbers')) {
    function format_lottery_numbers(array $numbers, string $separator = ', '): string
    {
        if (empty($numbers)) {
            return '';
        }
        
        try {
            sort($numbers);
            return implode($separator, $numbers);
        } catch (\Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('format_currency_polish')) {
    function format_currency_polish(float $amount): string
    {
        try {
            return number_format($amount, 2, ',', ' ') . ' zł';
        } catch (\Throwable $e) {
            return '0,00 zł';
        }
    }
}

if (!function_exists('get_game_icon')) {
    function get_game_icon(string $gameSlug): string
    {
        $icons = [
            'lotto' => 'fas fa-circle',
            'multi-multi' => 'fas fa-th',
            'eurojackpot' => 'fas fa-star'
        ];
        
        return $icons[$gameSlug] ?? 'fas fa-dice';
    }
}


if (!function_exists('validate_csv_structure')) {
    /**
     * Waliduje strukturę pliku CSV dla losowań
     */
    function validate_csv_structure(string $csvPath): array
    {
        $errors = [];
        
        if (!file_exists($csvPath)) {
            $errors[] = 'Plik CSV nie istnieje';
            return $errors;
        }
        
        try {
            $handle = fopen($csvPath, 'r');
            if (!$handle) {
                $errors[] = 'Nie można otworzyć pliku CSV';
                return $errors;
            }
            
            // Sprawdzenie nagłówka (opcjonalne)
            $header = fgetcsv($handle);
            $expectedColumns = ['numer_losowania', 'data', 'godzina', 'liczby_a', 'liczby_b'];
            
            // Jeśli pierwszy wiersz wygląda jak nagłówek, sprawdź strukturę
            if ($header && !is_numeric($header[0])) {
                if (count($header) < 4) {
                    $errors[] = 'Nieprawidłowa liczba kolumn w nagłówku. Oczekiwane: ' . implode(', ', $expectedColumns);
                }
                // Pobierz następny wiersz jako pierwszy wiersz danych
                $firstDataRow = fgetcsv($handle);
            } else {
                // Pierwszy wiersz to już dane
                $firstDataRow = $header;
            }
            
            // Sprawdzenie pierwszych wierszy danych
            $rowNumber = 1;
            $dataRows = [$firstDataRow];
            
            // Pobierz kilka następnych wierszy do sprawdzenia
            while (($row = fgetcsv($handle)) !== false && $rowNumber < 5) {
                $dataRows[] = $row;
                $rowNumber++;
            }
            
            foreach ($dataRows as $index => $row) {
                if (!$row) continue;
                
                $currentRow = $index + 1;
                
                if (count($row) < 4) {
                    $errors[] = "Wiersz {$currentRow}: Zbyt mało kolumn (wymagane minimum 4)";
                    continue;
                }
                
                // Sprawdzenie numeru losowania
                if (!is_numeric($row[0]) || (int)$row[0] <= 0) {
                    $errors[] = "Wiersz {$currentRow}: Nieprawidłowy numer losowania '{$row[0]}'";
                }
                
                // Sprawdzenie daty
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $row[1])) {
                    $errors[] = "Wiersz {$currentRow}: Nieprawidłowy format daty '{$row[1]}'. Oczekiwany: YYYY-MM-DD";
                } else {
                    // Sprawdź czy data jest prawidłowa
                    $dateParts = explode('-', $row[1]);
                    if (count($dateParts) === 3) {
                        if (!checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                            $errors[] = "Wiersz {$currentRow}: Nieprawidłowa data '{$row[1]}'";
                        }
                    }
                }
                
                // Sprawdzenie godziny (opcjonalne)
                if (!empty($row[2]) && !preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $row[2])) {
                    $errors[] = "Wiersz {$currentRow}: Nieprawidłowy format godziny '{$row[2]}'. Oczekiwany: HH:MM lub HH:MM:SS";
                }
                
                // Sprawdzenie liczb głównych
                if (empty($row[3])) {
                    $errors[] = "Wiersz {$currentRow}: Brak liczb głównych";
                } else {
                    $numbers = parse_lottery_numbers($row[3]);
                    if (empty($numbers)) {
                        $errors[] = "Wiersz {$currentRow}: Nieprawidłowe liczby główne '{$row[3]}'";
                    } else if (count($numbers) < 3) {
                        $errors[] = "Wiersz {$currentRow}: Za mało liczb głównych (minimum 3)";
                    }
                }
                
                // Sprawdzenie liczb bonus (opcjonalne)
                if (isset($row[4]) && !empty($row[4])) {
                    $bonusNumbers = parse_lottery_numbers($row[4]);
                    if (empty($bonusNumbers)) {
                        $errors[] = "Wiersz {$currentRow}: Nieprawidłowe liczby bonus '{$row[4]}'";
                    }
                }
                
                // Limit błędów - nie sprawdzaj więcej jeśli już jest dużo błędów
                if (count($errors) >= 10) {
                    $errors[] = 'Zbyt wiele błędów. Sprawdź strukturę pliku.';
                    break;
                }
            }
            
            fclose($handle);
            
        } catch (\Throwable $e) {
            $errors[] = 'Błąd podczas sprawdzania pliku: ' . $e->getMessage();
        }
        
        return $errors;
    }
}

if (!function_exists('convert_eurojackpot_number')) {
    /**
     * Konwertuje numer EuroJackpot między numeracją polską a światową
     * EuroJackpot 15 września 2017: świat - numer 287, Polska - numer 1
     */
    function convert_eurojackpot_number(int $number, string $from = 'polish', string $to = 'global'): ?int
    {
        // Data rozpoczęcia EuroJackpot w Polsce: 15 września 2017 (piątek)
        // Pierwszy polski numer: 1, odpowiadający globalnemu numerowi: 287
        $polishStartNumber = 1;
        $globalStartNumber = 287;
        $offset = $globalStartNumber - $polishStartNumber; // 286
        
        if ($from === 'polish' && $to === 'global') {
            return $number + $offset;
        } elseif ($from === 'global' && $to === 'polish') {
            $polishNumber = $number - $offset;
            return $polishNumber > 0 ? $polishNumber : null;
        }
        
        return $number;
    }
}