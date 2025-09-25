<?php

// ==========================================
// app/Helpers/lottery_helper.php
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

if (!function_exists('parse_lottery_numbers')) {
    /**
     * Parsuje string liczb do tablicy
     */
    function parse_lottery_numbers(string $numbersString): array
    {
        if (empty($numbersString)) {
            return [];
        }
        
        // Obsługa różnych separatorów
        $numbers = preg_split('/[,;\s]+/', trim($numbersString));
        $numbers = array_map('intval', array_filter($numbers, 'is_numeric'));
        
        return array_unique($numbers);
    }
}

if (!function_exists('validate_lottery_numbers')) {
    /**
     * Waliduje czy liczby są prawidłowe dla zakresu gry
     */
    function validate_lottery_numbers(array $numbers, int $minNumber, int $maxNumber, int $requiredCount = null): array
    {
        $errors = [];
        
        // Sprawdzenie czy wszystkie liczby są w zakresie
        foreach ($numbers as $number) {
            if ($number < $minNumber || $number > $maxNumber) {
                $errors[] = "Liczba {$number} jest poza zakresem {$minNumber}-{$maxNumber}";
            }
        }
        
        // Sprawdzenie czy nie ma duplikatów
        if (count($numbers) !== count(array_unique($numbers))) {
            $errors[] = 'Liczby nie mogą się powtarzać';
        }
        
        // Sprawdzenie ilości liczb (jeśli wymagana)
        if ($requiredCount !== null && count($numbers) !== $requiredCount) {
            $errors[] = "Wymagane jest dokładnie {$requiredCount} liczb, podano " . count($numbers);
        }
        
        return $errors;
    }
}

if (!function_exists('generate_random_numbers')) {
    /**
     * Generuje losowe liczby dla gry
     */
    function generate_random_numbers(int $minNumber, int $maxNumber, int $count): array
    {
        if ($count > ($maxNumber - $minNumber + 1)) {
            throw new InvalidArgumentException('Nie można wylosować więcej liczb niż dostępnych w zakresie');
        }
        
        $available = range($minNumber, $maxNumber);
        shuffle($available);
        
        return array_slice($available, 0, $count);
    }
}

if (!function_exists('calculate_lottery_matches')) {
    /**
     * Oblicza ile liczb trafiło między dwoma zestawami
     */
    function calculate_lottery_matches(array $betNumbers, array $drawNumbers): int
    {
        return count(array_intersect($betNumbers, $drawNumbers));
    }
}

if (!function_exists('calculate_lottery_combinations')) {
    /**
     * Oblicza liczbę kombinacji (n choose k)
     */
    function calculate_lottery_combinations(int $n, int $k): int
    {
        if ($k > $n || $k < 0) {
            return 0;
        }
        
        if ($k === 0 || $k === $n) {
            return 1;
        }
        
        // Optymalizacja: C(n,k) = C(n, n-k)
        if ($k > $n - $k) {
            $k = $n - $k;
        }
        
        $result = 1;
        for ($i = 0; $i < $k; $i++) {
            $result = $result * ($n - $i) / ($i + 1);
        }
        
        return (int)round($result);
    }
}

if (!function_exists('calculate_lottery_probability')) {
    /**
     * Oblicza prawdopodobieństwo trafienia określonej liczby liczb
     */
    function calculate_lottery_probability(int $totalNumbers, int $drawnNumbers, int $betNumbers, int $matches): float
    {
        // Prawdopodobieństwo hipergeometryczne
        // P(X = k) = C(K,k) * C(N-K,n-k) / C(N,n)
        // gdzie: N = totalNumbers, K = drawnNumbers, n = betNumbers, k = matches
        
        $numerator = calculate_lottery_combinations($drawnNumbers, $matches) * 
                    calculate_lottery_combinations($totalNumbers - $drawnNumbers, $betNumbers - $matches);
        $denominator = calculate_lottery_combinations($totalNumbers, $betNumbers);
        
        return $denominator > 0 ? $numerator / $denominator : 0;
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

if (!function_exists('calculate_roi')) {
    /**
     * Oblicza zwrot z inwestycji (ROI) w procentach
     */
    function calculate_roi(float $profit, float $investment): float
    {
        if ($investment <= 0) {
            return 0;
        }
        
        return round(($profit / $investment) * 100, 2);
    }
}

if (!function_exists('get_lottery_colors')) {
    /**
     * Zwraca kolory dla różnych poziomów trafień
     */
    function get_lottery_colors(): array
    {
        return [
            'hot' => '#dc3545',     // Czerwony dla hot numbers
            'cold' => '#007bff',    // Niebieski dla cold numbers
            'match' => '#28a745',   // Zielony dla trafień
            'miss' => '#6c757d',    // Szary dla nietrafień
            'jackpot' => '#ffc107'  // Żółty dla jackpotu
        ];
    }
}

if (!function_exists('generate_lottery_ticket_html')) {
    /**
     * Generuje HTML dla kuponu loterii
     */
    function generate_lottery_ticket_html(array $numbers, array $ranges, array $drawnNumbers = []): string
    {
        $html = '<div class="lottery-ticket">';
        
        foreach ($ranges as $range) {
            $rangeNumbers = $numbers[$range['range_name']] ?? [];
            $drawnRangeNumbers = $drawnNumbers[$range['range_name']] ?? [];
            
            $html .= '<div class="lottery-range">';
            $html .= '<h6>' . ucfirst($range['range_name']) . ' (' . $range['min_number'] . '-' . $range['max_number'] . ')</h6>';
            $html .= '<div class="numbers-grid">';
            
            for ($i = $range['min_number']; $i <= $range['max_number']; $i++) {
                $isSelected = in_array($i, $rangeNumbers);
                $isDrawn = in_array($i, $drawnRangeNumbers);
                $isMatch = $isSelected && $isDrawn;
                
                $classes = ['number-cell'];
                if ($isSelected) $classes[] = 'selected';
                if ($isDrawn) $classes[] = 'drawn';
                if ($isMatch) $classes[] = 'match';
                
                $html .= '<span class="' . implode(' ', $classes) . '">' . $i . '</span>';
            }
            
            $html .= '</div></div>';
        }
        
        $html .= '</div>';
        return $html;
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

if (!function_exists('calculate_expected_value')) {
    /**
     * Oblicza oczekiwaną wartość dla zakładu
     */
    function calculate_expected_value(array $prizes, array $probabilities, float $betCost): float
    {
        $expectedWin = 0;
        
        foreach ($prizes as $index => $prize) {
            $probability = $probabilities[$index] ?? 0;
            $expectedWin += $prize * $probability;
        }
        
        return $expectedWin - $betCost;
    }
}

if (!function_exists('get_frequency_color')) {
    /**
     * Zwraca kolor na podstawie częstości wystąpień
     */
    function get_frequency_color(int $frequency, int $maxFrequency): string
    {
        if ($maxFrequency === 0) {
            return '#f8f9fa';
        }
        
        $ratio = $frequency / $maxFrequency;
        
        if ($ratio >= 0.8) return '#dc3545'; // Bardzo częste - czerwony
        if ($ratio >= 0.6) return '#fd7e14'; // Częste - pomarańczowy  
        if ($ratio >= 0.4) return '#ffc107'; // Średnie - żółty
        if ($ratio >= 0.2) return '#20c997'; // Rzadkie - zielony
        return '#6c757d';                    // Bardzo rzadkie - szary
    }
}

if (!function_exists('format_processing_time')) {
    /**
     * Formatuje czas przetwarzania
     */
    function format_processing_time(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' sek.';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes < 60) {
            return $minutes . ' min. ' . $remainingSeconds . ' sek.';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $hours . ' godz. ' . $remainingMinutes . ' min.';
    }
}

if (!function_exists('estimate_processing_time')) {
    /**
     * Szacuje czas przetwarzania na podstawie ilości danych
     */
    function estimate_processing_time(int $drawsCount, string $modelType): int
    {
        // Szacowany czas w sekundach na 1000 losowań
        $timePerThousandDraws = match($modelType) {
            'repeat_count' => 5,
            'frequent_numbers' => 3,
            'probabilistic' => 10,
            'ensemble' => 15,
            'ev_optimization' => 25,
            'gap_analysis' => 8,
            default => 10
        };
        
        return ceil(($drawsCount / 1000) * $timePerThousandDraws);
    }
}

if (!function_exists('convert_eurojackpot_number')) {
    /**
     * Konwertuje numer EuroJackpot między numeracją polską a światową
     * EuroJackpot 15 września 2017: świat - numer 287, Polska - numer 1
     */
    function convert_eurojackpot_number(int $number, string $from = 'polish', string $to = 'global'): int
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

if (!function_exists('validate_csv_structure')) {
    /**
     * Waliduje strukturę pliku CSV
     */
    function validate_csv_structure(string $csvPath): array
    {
        $errors = [];
        
        if (!file_exists($csvPath)) {
            $errors[] = 'Plik CSV nie istnieje';
            return $errors;
        }
        
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $errors[] = 'Nie można otworzyć pliku CSV';
            return $errors;
        }
        
        // Sprawdzenie nagłówka
        $header = fgetcsv($handle);
        $expectedColumns = ['numer_losowania', 'data', 'godzina', 'liczby_a', 'liczby_b'];
        
        if (!$header || count($header) < 4) {
            $errors[] = 'Nieprawidłowa struktura nagłówka CSV. Oczekiwane kolumny: ' . implode(', ', $expectedColumns);
        }
        
        // Sprawdzenie kilku pierwszych wierszy
        $rowNumber = 2;
        while (($row = fgetcsv($handle)) !== false && $rowNumber <= 10) {
            if (count($row) < 4) {
                $errors[] = "Wiersz {$rowNumber}: Zbyt mało kolumn";
            }
            
            // Sprawdzenie numeru losowania
            if (!is_numeric($row[0])) {
                $errors[] = "Wiersz {$rowNumber}: Numer losowania musi być liczbą";
            }
            
            // Sprawdzenie daty
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $row[1])) {
                $errors[] = "Wiersz {$rowNumber}: Data musi być w formacie YYYY-MM-DD";
            }
            
            // Sprawdzenie liczb
            $numbers = parse_lottery_numbers($row[3]);
            if (empty($numbers)) {
                $errors[] = "Wiersz {$rowNumber}: Brak prawidłowych liczb w kolumnie liczby_a";
            }
            
            $rowNumber++;
        }
        
        fclose($handle);
        return $errors;
    }
}

if (!function_exists('get_progress_bar_class')) {
    /**
     * Zwraca klasę CSS dla paska postępu
     */
    function get_progress_bar_class(float $percentage): string
    {
        if ($percentage >= 80) return 'bg-success';
        if ($percentage >= 60) return 'bg-info';
        if ($percentage >= 40) return 'bg-warning';
        return 'bg-danger';
    }
}

if (!function_exists('format_accuracy_badge')) {
    /**
     * Formatuje znaczek dokładności
     */
    function format_accuracy_badge(?float $accuracy): string
    {
        if ($accuracy === null) {
            return '<span class="badge bg-secondary">Brak danych</span>';
        }
        
        $class = match(true) {
            $accuracy >= 80 => 'bg-success',
            $accuracy >= 60 => 'bg-info', 
            $accuracy >= 40 => 'bg-warning',
            default => 'bg-danger'
        };
        
        return '<span class="badge ' . $class . '">' . number_format($accuracy, 1) . '%</span>';
    }
}

if (!function_exists('get_status_badge')) {
    /**
     * Zwraca znaczek statusu
     */
    function get_status_badge(string $status): string
    {
        $badges = [
            'waiting' => '<span class="badge bg-secondary">Oczekuje</span>',
            'processing' => '<span class="badge bg-primary">Przetwarzanie</span>',
            'completed' => '<span class="badge bg-success">Zakończone</span>',
            'error' => '<span class="badge bg-danger">Błąd</span>',
            'generating' => '<span class="badge bg-info">Generowanie</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge bg-dark">' . ucfirst($status) . '</span>';
    }
}