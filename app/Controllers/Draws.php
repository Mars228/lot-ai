<?php

// ==========================================
// app/Controllers/Draws.php
// ==========================================

namespace App\Controllers;

class Draws extends BaseController
{
    protected $drawModel;
    protected $gameModel;
    protected $settingsModel;

    public function __construct()
    {
        // Modele będą ładowane lazy loading
    }

    /**
     * Lista losowań (READ) - domyślnie ostatni miesiąc Lotto
     */
    public function index(?string $gameSlug = null)
    {
        try {
            $this->gameModel = model('GameModel');
            $this->drawModel = model('DrawModel');

            // Pobierz wszystkie gry dla filtru
            $games = $this->gameModel->where('is_active', 1)->findAll();
            
            if (empty($games)) {
                $this->setMessage('Brak gier w systemie. Dodaj najpierw gry liczbowe.', 'warning');
                return redirect()->to('/games');
            }

            // Określ aktywną grę
            if ($gameSlug) {
                $currentGame = $this->gameModel->findBySlug($gameSlug);
                if (!$currentGame) {
                    $this->setMessage('Gra nie została znaleziona', 'error');
                    return redirect()->to('/draws');
                }
            } else {
                // Domyślnie pierwsza gra (Lotto)
                $currentGame = $games[0];
            }

            // Parametry filtrowania z GET
            $filters = [
                'year' => $this->request->getGet('year') ?: date('Y'),
                'month' => $this->request->getGet('month') ?: date('m'),
                'page' => max(1, (int)($this->request->getGet('page') ?: 1))
            ];

            // Oblicz zakres dat dla wybranego miesiąca
            $startDate = sprintf('%04d-%02d-01', $filters['year'], $filters['month']);
            $endDate = date('Y-m-t', strtotime($startDate));

            // Pobierz losowania
            $draws = $this->drawModel->getDrawsByDateRange($currentGame['id'], $startDate, $endDate);
            
            // Informacje o zakresie numerów losowań
            $drawNumberRange = $this->drawModel->getDrawNumberRange($currentGame['id']);
            
            // Sprawdź źródło danych ostatniego importu
            $latestDraw = $this->drawModel->getLatestDrawForGame($currentGame['id']);
            
            // Statystyki
            $stats = [
                'total_draws' => $this->drawModel->getTotalDrawsForGame($currentGame['id']),
                'current_month_draws' => count($draws),
                'oldest_draw' => $drawNumberRange['min'],
                'latest_draw' => $drawNumberRange['max'],
                'last_import_source' => $latestDraw['data_source'] ?? 'brak',
                'last_import_date' => $latestDraw['updated_at'] ?? 'brak'
            ];

            // Generuj opcje dla selektorów rok/miesiąc
            $yearOptions = $this->generateYearOptions();
            $monthOptions = $this->generateMonthOptions();

            $data = array_merge($this->viewData, [
                'pageTitle' => "Losowania - {$currentGame['name']}",
                'currentGame' => $currentGame,
                'games' => $games,
                'draws' => $draws,
                'filters' => $filters,
                'stats' => $stats,
                'yearOptions' => $yearOptions,
                'monthOptions' => $monthOptions,
                'breadcrumbs' => [
                    ['title' => 'Strona główna', 'url' => base_url('/')],
                    ['title' => 'Losowania']
                ]
            ]);

            return view('draws/index', $data);

        } catch (\Throwable $e) {
            log_message('error', 'Błąd w Draws::index(): ' . $e->getMessage());
            $this->setMessage('Błąd podczas ładowania losowań: ' . $e->getMessage(), 'error');
            return redirect()->to('/');
        }
    }

    /**
     * Import plików CSV (CREATE)
     */
    public function import()
    {
        if (!$this->request->is('post')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Nieprawidłowa metoda'], 405);
        }

        try {
            // Walidacja danych
            $gameId = (int)$this->request->getPost('game_id');
            if (!$gameId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Wybierz grę'], 400);
            }

            // Sprawdź czy gra istnieje
            $this->gameModel = model('GameModel');
            $game = $this->gameModel->find($gameId);
            if (!$game) {
                return $this->jsonResponse(['success' => false, 'message' => 'Gra nie została znaleziona'], 404);
            }

            // Obsługa pliku CSV
            $csvFile = $this->request->getFile('csv_file');
            if (!$csvFile || !$csvFile->isValid()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Nie przesłano pliku lub plik jest uszkodzony'], 400);
            }

            // Walidacja typu pliku
            if ($csvFile->getClientExtension() !== 'csv') {
                return $this->jsonResponse(['success' => false, 'message' => 'Można przesłać tylko pliki CSV'], 400);
            }

            // Walidacja rozmiaru (max 10MB)
            if ($csvFile->getSize() > 10 * 1024 * 1024) {
                return $this->jsonResponse(['success' => false, 'message' => 'Plik jest za duży (max 10MB)'], 400);
            }

            // Przenieś plik do uploads/csv
            $uploadsPath = FCPATH . '/uploads/csv/';
            if (!is_dir($uploadsPath)) {
                mkdir($uploadsPath, 0755, true);
            }

            $fileName = $game['slug'] . '_' . date('Y-m-d_H-i-s') . '.csv';
            $csvFile->move($uploadsPath, $fileName);
            $filePath = $uploadsPath . $fileName;

            // Walidacja struktury CSV
            $csvErrors = helper('lottery_helper') ? validate_csv_structure($filePath) : [];
            if (!empty($csvErrors)) {
                unlink($filePath); // Usuń nieprawidłowy plik
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Nieprawidłowa struktura CSV',
                    'errors' => $csvErrors
                ], 400);
            }

            // Import danych
            $this->drawModel = model('DrawModel');
            $result = $this->drawModel->importFromCsv($gameId, $filePath);

            // Aktualizuj ostatnią synchronizację
            $this->settingsModel = model('SettingsModel');
            $this->settingsModel->setValue('last_sync_date', date('Y-m-d H:i:s'));

            // Zachowaj plik tylko jeśli import się powiódł
            if ($result['imported'] > 0 || $result['updated'] > 0) {
                // Plik zostaje zachowany
                $message = sprintf(
                    'Import zakończony: %d nowych, %d zaktualizowanych', 
                    $result['imported'], 
                    $result['updated']
                );
                if (!empty($result['errors'])) {
                    $message .= sprintf(', %d błędów', count($result['errors']));
                }
            } else {
                unlink($filePath); // Usuń plik jeśli nic nie zaimportowano
                $message = 'Nie zaimportowano żadnych danych';
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => $message,
                'data' => $result
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Błąd w Draws::import(): ' . $e->getMessage());
            
            // Usuń plik w przypadku błędu
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }

            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Błąd podczas importu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronizacja z Lotto OpenAPI
     */
    public function sync()
    {
        if (!$this->request->is('post')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Nieprawidłowa metoda'], 405);
        }

        try {
            $gameId = (int)$this->request->getPost('game_id');
            $drawNumber = $this->request->getPost('draw_number');

            if (!$gameId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Wybierz grę'], 400);
            }

            // Sprawdź czy gra istnieje i ma API
            $this->gameModel = model('GameModel');
            $game = $this->gameModel->find($gameId);
            if (!$game || !$game['api_game_type']) {
                return $this->jsonResponse(['success' => false, 'message' => 'Gra nie obsługuje synchronizacji API'], 400);
            }

            // Pobierz API key
            $this->settingsModel = model('SettingsModel');
            $apiKey = $this->settingsModel->getValue('lotto_api_key');
            if (!$apiKey) {
                return $this->jsonResponse(['success' => false, 'message' => 'Brak klucza API. Skonfiguruj w ustawieniach.'], 400);
            }

            // Inicjalizuj API service
            $apiService = new \App\Services\LottoApiService($apiKey);

            if ($drawNumber) {
                // Synchronizacja konkretnego losowania
                $result = $apiService->syncSingleDraw($game, (int)$drawNumber);
            } else {
                // Synchronizacja najnowszego losowania
                $result = $apiService->syncLatestDraw($game);
            }

            if ($result['success']) {
                // Aktualizuj ostatnią synchronizację
                $this->settingsModel->setValue('last_sync_date', date('Y-m-d H:i:s'));

                return $this->jsonResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data']
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Throwable $e) {
            log_message('error', 'Błąd w Draws::sync(): ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Błąd podczas synchronizacji: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Uzupełnianie brakujących losowań
     */
    public function fillMissing()
    {
        if (!$this->request->is('post')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Nieprawidłowa metoda'], 405);
        }

        try {
            $gameId = (int)$this->request->getPost('game_id');
            $startNumber = (int)$this->request->getPost('start_number');
            $endNumber = (int)$this->request->getPost('end_number');

            // Walidacja
            if (!$gameId || !$startNumber || !$endNumber) {
                return $this->jsonResponse(['success' => false, 'message' => 'Wszystkie pola są wymagane'], 400);
            }

            if ($startNumber > $endNumber) {
                return $this->jsonResponse(['success' => false, 'message' => 'Numer początkowy nie może być większy od końcowego'], 400);
            }

            if (($endNumber - $startNumber) > 1000) {
                return $this->jsonResponse(['success' => false, 'message' => 'Maksymalny zakres to 1000 losowań'], 400);
            }

            // Sprawdź grę
            $this->gameModel = model('GameModel');
            $game = $this->gameModel->find($gameId);
            if (!$game || !$game['api_game_type']) {
                return $this->jsonResponse(['success' => false, 'message' => 'Gra nie obsługuje synchronizacji API'], 400);
            }

            // Znajdź brakujące numery
            $this->drawModel = model('DrawModel');
            $missingNumbers = $this->drawModel->getMissingDrawNumbers($gameId, $startNumber, $endNumber);

            if (empty($missingNumbers)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Wszystkie losowania w zakresie już istnieją',
                    'data' => ['filled' => 0, 'errors' => []]
                ]);
            }

            // Pobierz API key
            $this->settingsModel = model('SettingsModel');
            $apiKey = $this->settingsModel->getValue('lotto_api_key');
            if (!$apiKey) {
                return $this->jsonResponse(['success' => false, 'message' => 'Brak klucza API'], 400);
            }

            // Uruchom uzupełnianie w tle lub w pętli
            $apiService = new \App\Services\LottoApiService($apiKey);
            $result = $apiService->fillMissingDraws($game, $missingNumbers);

            return $this->jsonResponse([
                'success' => true,
                'message' => sprintf('Uzupełniono %d losowań', $result['filled']),
                'data' => $result
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Błąd w Draws::fillMissing(): ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Błąd podczas uzupełniania: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Status synchronizacji (dla AJAX progress)
     */
    public function syncStatus()
    {
        try {
            $this->settingsModel = model('SettingsModel');
            $lastSync = $this->settingsModel->getValue('last_sync_date');
            
            // Sprawdź czy trwa jakiś proces synchronizacji
            $syncInProgress = $this->settingsModel->getValue('sync_in_progress', false);

            return $this->jsonResponse([
                'success' => true,
                'last_sync' => $lastSync,
                'in_progress' => (bool)$syncInProgress,
                'formatted_date' => $lastSync ? format_datetime_polish($lastSync) : 'Nigdy'
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Błąd w Draws::syncStatus(): ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Generuje opcje lat dla selectora
     */
    private function generateYearOptions(): array
    {
        $currentYear = (int)date('Y');
        $startYear = 1957; // Rok rozpoczęcia gier
        $years = [];

        for ($year = $currentYear; $year >= $startYear; $year--) {
            $years[$year] = $year;
        }

        return $years;
    }

    /**
     * Generuje opcje miesięcy dla selectora
     */
    private function generateMonthOptions(): array
    {
        return [
            1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
            5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
            9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
        ];
    }
}

// ==========================================
// app/Services/LottoApiService.php - Serwis do komunikacji z API
// ==========================================

namespace App\Services;

class LottoApiService
{
    private string $apiKey;
    private string $baseUrl = 'https://developers.lotto.pl/api/open/v1';
    private array $headers;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->headers = [
            'Accept: application/json',
            'Secret: ' . $this->apiKey
        ];
    }

    /**
     * Synchronizuje najnowsze losowanie
     */
    public function syncLatestDraw(array $game): array
    {
        try {
            // Pobierz informacje o grze z API
            $gameInfo = $this->getGameInfo($game['api_game_type']);
            if (!$gameInfo['success']) {
                return $gameInfo;
            }

            // Pobierz najnowsze losowanie
            $today = date('Y-m-d\T20:00\Z');
            $drawData = $this->getDrawByDate($game['api_game_type'], $today);
            
            if ($drawData['success'] && !empty($drawData['data'])) {
                $draw = $drawData['data'][0]; // Pierwsze losowanie z dnia
                
                // Zapisz do bazy
                $result = $this->saveDraw($game, $draw);
                
                return [
                    'success' => true,
                    'message' => 'Zsynchronizowano najnowsze losowanie',
                    'data' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Brak danych losowania na dziś'
                ];
            }

        } catch (\Throwable $e) {
            log_message('error', 'LottoApiService::syncLatestDraw error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Błąd API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Synchronizuje konkretne losowanie
     */
    public function syncSingleDraw(array $game, int $drawNumber): array
    {
        try {
            // Dla EuroJackpot konwertuj numerację polską na światową jeśli potrzeba
            if ($game['api_game_type'] === 'EuroJackpot') {
                $globalDrawNumber = convert_eurojackpot_number($drawNumber, 'polish', 'global');
                if ($globalDrawNumber === null) {
                    return [
                        'success' => false,
                        'message' => 'Nieprawidłowy numer losowania EuroJackpot'
                    ];
                }
            } else {
                $globalDrawNumber = $drawNumber;
            }

            // TODO: Implementacja pobierania konkretnego losowania z API
            // API Lotto nie ma bezpośredniego endpointa dla numeru losowania
            // Trzeba będzie szukać po dacie lub implementować własną logikę

            return [
                'success' => false,
                'message' => 'Funkcja w przygotowaniu - API nie obsługuje pobierania po numerze'
            ];

        } catch (\Throwable $e) {
            log_message('error', 'LottoApiService::syncSingleDraw error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Błąd API: ' . $e->getMessage()
            ];
        }
    }

    /**
 * Sprawdza brakujące losowania między bazą a API
 */
public function checkMissingDraws()
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Nieprawidłowa metoda'
            ]);
        }
        
        try {
            $gameId = (int)$this->request->getPost('game_id');
            
            if (!$gameId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nie wybrano gry'
                ]);
            }
            
            // Pobierz dane gry
            $gameModel = model('GameModel');
            $game = $gameModel->find($gameId);
            
            if (!$game || !$game['api_game_type']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gra nie obsługuje API'
                ]);
            }
            
            // Pobierz ostatnie losowanie z bazy
            $db = \Config\Database::connect();
            $lastInDb = $db->table('draws')
                           ->where('game_id', $gameId)
                           ->orderBy('draw_number', 'DESC')
                           ->limit(1)
                           ->get()
                           ->getRow();
            
            // Pobierz ostatnie dostępne losowanie z API
            $apiKey = 'LlQ2nogAEteK1zd7SkD5IGyPQt7phMdeVr0ZLLfHdiE=';
            $lastInApi = $this->getLatestDrawFromAPI($game['api_game_type'], $apiKey);
            
            if (!$lastInApi) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nie udało się pobrać danych z API'
                ]);
            }
            
            // Przygotuj informacje o brakach
            $lastDbNumber = $lastInDb ? (int)$lastInDb->draw_number : 0;
            $lastApiNumber = (int)$lastInApi['draw_number'];
            
            $missingNumbers = [];
            if ($lastDbNumber < $lastApiNumber) {
                // Znajdź wszystkie brakujące numery
                for ($i = $lastDbNumber + 1; $i <= $lastApiNumber; $i++) {
                    // Sprawdź czy numer już istnieje w bazie
                    $exists = $db->table('draws')
                                ->where('game_id', $gameId)
                                ->where('draw_number', $i)
                                ->countAllResults() > 0;
                    
                    if (!$exists) {
                        $missingNumbers[] = $i;
                    }
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'last_in_db' => $lastDbNumber,
                'last_date_db' => $lastInDb ? $lastInDb->draw_date : 'brak danych',
                'last_in_api' => $lastApiNumber,
                'last_date_api' => $lastInApi['draw_date'],
                'missing_count' => count($missingNumbers),
                'missing_numbers' => $missingNumbers
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'CheckMissingDraws error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Pobiera ostatnie dostępne losowanie z API
     */
    private function getLatestDrawFromAPI($gameType, $apiKey)
    {
        // Użyj dzisiejszej daty
        $today = date('Y-m-d\T20:00\Z');
        
        $url = "https://developers.lotto.pl/api/open/v1/lotteries/draw-results/by-date-per-game"
             . "?gameType={$gameType}"
             . "&drawDate={$today}"
             . "&sort=drawSystemId"
             . "&order=DESC"
             . "&index=1&size=1";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Secret: ' . $apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['items']) && !empty($data['items'])) {
                $draw = $data['items'][0];
                return [
                    'draw_number' => $draw['drawSystemId'],
                    'draw_date' => date('Y-m-d', strtotime($draw['drawDate']))
                ];
            }
        }
        
        // Jeśli nie ma dzisiaj, spróbuj wczoraj
        $yesterday = date('Y-m-d\T20:00\Z', strtotime('-1 day'));
        $url = str_replace($today, $yesterday, $url);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Secret: ' . $apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['items']) && !empty($data['items'])) {
                $draw = $data['items'][0];
                return [
                    'draw_number' => $draw['drawSystemId'],
                    'draw_date' => date('Y-m-d', strtotime($draw['drawDate']))
                ];
            }
        }
        
        return null;
    }

    /**
     * Pobiera i zapisuje pojedyncze losowanie
     */
    public function fillSingleDraw()
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nieprawidłowa metoda'
            ]);
        }
        
        try {
            $gameId = (int)$this->request->getPost('game_id');
            $drawNumber = (int)$this->request->getPost('draw_number');
            
            if (!$gameId || !$drawNumber) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Brak wymaganych parametrów'
                ]);
            }
            
            // Pobierz dane gry
            $gameModel = model('GameModel');
            $game = $gameModel->find($gameId);
            
            if (!$game || !$game['api_game_type']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gra nie obsługuje API'
                ]);
            }
            
            $apiKey = 'LlQ2nogAEteK1zd7SkD5IGyPQt7phMdeVr0ZLLfHdiE=';
            
            // Pobierz losowanie z API po numerze
            $url = "https://developers.lotto.pl/api/open/v1/lotteries/draw-results/by-gametype"
                 . "?gameType={$game['api_game_type']}"
                 . "&drawSystemId={$drawNumber}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Secret: ' . $apiKey
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "API zwróciło błąd HTTP {$httpCode}"
                ]);
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['items']) || empty($data['items'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Brak danych dla losowania #{$drawNumber}"
                ]);
            }
            
            $apiDraw = $data['items'][0];
            
            // Przygotuj dane do zapisu
            $drawData = [
                'game_id' => $gameId,
                'draw_number' => $drawNumber,
                'draw_date' => date('Y-m-d', strtotime($apiDraw['drawDate'])),
                'draw_time' => date('H:i:s', strtotime($apiDraw['drawDate'])),
                'numbers_a' => implode(',', $apiDraw['results'] ?? []),
                'numbers_b' => null,
                'source' => 'api'
            ];
            
            // Dla EuroJackpot - dodatkowe liczby
            if ($game['api_game_type'] === 'EuroJackpot' && isset($apiDraw['specialResults'])) {
                $drawData['numbers_b'] = implode(',', $apiDraw['specialResults']);
            }
            
            // Zapisz do bazy
            $drawModel = model('DrawModel');
            
            // Sprawdź czy już istnieje
            $existing = $drawModel->where('game_id', $gameId)
                                  ->where('draw_number', $drawNumber)
                                  ->first();
            
            if ($existing) {
                $drawModel->update($existing['id'], $drawData);
                $message = "Zaktualizowano losowanie #{$drawNumber}";
            } else {
                $drawModel->insert($drawData);
                $message = "Dodano losowanie #{$drawNumber}";
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'FillSingleDraw error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Pobiera informacje o grze
     */
    private function getGameInfo(string $gameType): array
    {
        $url = $this->baseUrl . '/lotteries/info?gameType=' . urlencode($gameType);
        return $this->makeApiCall($url);
    }

    /**
     * Pobiera losowania z konkretnej daty
     */
    private function getDrawByDate(string $gameType, string $date): array
    {
        $url = $this->baseUrl . '/lotteries/draw-results/by-date-per-game?' . http_build_query([
            'gameType' => $gameType,
            'drawDate' => $date,
            'sort' => 'drawSystemId',
            'order' => 'DESC',
            'index' => 1,
            'size' => 10
        ]);
        
        return $this->makeApiCall($url);
    }

    /**
     * Wykonuje zapytanie do API
     */
    private function makeApiCall(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new \Exception("HTTP error: {$httpCode}");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: " . json_last_error_msg());
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Zapisuje dane losowania do bazy
     */
    private function saveDraw(array $game, array $drawData): array
    {
        $drawModel = model('DrawModel');

        // Mapowanie danych z API na format bazy
        $saveData = [
            'game_id' => $game['id'],
            'draw_number' => $drawData['drawSystemId'] ?? 0,
            'draw_date' => date('Y-m-d', strtotime($drawData['drawDate'] ?? 'now')),
            'draw_time' => date('H:i:s', strtotime($drawData['drawDate'] ?? 'now')),
            'numbers_main' => json_encode($drawData['resultsJson']['mainNumbers'] ?? []),
            'numbers_bonus' => isset($drawData['resultsJson']['bonusNumbers']) ? 
                json_encode($drawData['resultsJson']['bonusNumbers']) : null,
            'jackpot_amount' => $drawData['jackpot'] ?? null,
            'data_source' => 'api'
        ];

        // Sprawdź czy losowanie już istnieje
        if ($drawModel->drawExists($game['id'], $saveData['draw_number'])) {
            // Aktualizuj istniejące
            $drawModel->where('game_id', $game['id'])
                     ->where('draw_number', $saveData['draw_number'])
                     ->set($saveData)
                     ->update();
            return ['action' => 'updated', 'draw_number' => $saveData['draw_number']];
        } else {
            // Dodaj nowe
            $drawModel->insert($saveData);
            return ['action' => 'inserted', 'draw_number' => $saveData['draw_number']];
        }
    }



public function test()
{
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Test działa!'
    ]);
}





}