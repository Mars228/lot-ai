<?php 

// ==========================================
// app/Controllers/Games.php
// ==========================================

namespace App\Controllers;

class Games extends BaseController
{
    protected $gameModel;
    protected $gameRangeModel;
    protected $gamePrizeModel;

    public function __construct()
    {
        // Nie ładujemy modeli w konstruktorze - będą załadowane lazy loading
    }

    /**
     * Lista wszystkich gier (READ)
     */
    public function index()
    {
        // Załaduj podstawowe dane widoku
        $this->loadViewData();

        try {
            $this->gameModel = model('GameModel');
            $games = $this->gameModel->getGamesWithDetails();
        } catch (\Exception $e) {
            log_message('error', 'Błąd w Games::index(): ' . $e->getMessage());
            $games = [];
            $this->setMessage('Błąd podczas ładowania gier: ' . $e->getMessage(), 'error');
        }

        $data = array_merge($this->viewData, [
            'pageTitle' => 'Gry liczbowe',
            'games' => $games
        ]);

        return view('games/index', $data);
    }

    /**
     * Formularz dodawania nowej gry (CREATE - form)
     */
    public function add()
    {
        $this->loadViewData();

        $data = array_merge($this->viewData, [
            'pageTitle' => 'Dodaj nową grę',
            'game' => null,
            'ranges' => [],
            'prizes' => []
        ]);

        return view('games/form', $data);
    }

    /**
     * Zapisywanie nowej gry (CREATE - save)
     */
    public function create()
    {
        // Inicjalizuj modele
        $this->gameModel = model('GameModel');
        $this->gameRangeModel = model('GameRangeModel');
        $this->gamePrizeModel = model('GamePrizeModel');

        // Walidacja danych
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[2]|max_length[100]',
            'slug' => 'required|alpha_dash|max_length[50]|is_unique[games.slug]',
            'bet_price' => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[1000]',
            'api_game_type' => 'permit_empty|max_length[50]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Obsługa upload logo
            $logoFilename = $this->handleLogoUpload();

            // Zapisywanie gry
            $gameData = [
                'name' => $this->request->getPost('name'),
                'slug' => $this->request->getPost('slug'),
                'description' => $this->request->getPost('description'),
                'bet_price' => $this->request->getPost('bet_price'),
                'api_game_type' => $this->request->getPost('api_game_type'),
                'logo_filename' => $logoFilename,
                'is_active' => 1
            ];

            $gameId = $this->gameModel->insert($gameData);

            // Zapisywanie zakresów liczb
            $this->saveGameRanges($gameId);

            // Zapisywanie poziomów wygranych
            $this->saveGamePrizes($gameId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Błąd podczas zapisywania do bazy danych');
            }

            $this->setMessage('Gra została pomyślnie dodana', 'success');
            return redirect()->to('/games');

        } catch (\Exception $e) {
            $db->transRollback();
            $this->setMessage('Błąd: ' . $e->getMessage(), 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Formularz edycji gry (UPDATE - form)
     */
    public function edit(int $id)
    {
        $this->loadViewData();
        $this->gameModel = model('GameModel');
        $this->gameRangeModel = model('GameRangeModel');
        $this->gamePrizeModel = model('GamePrizeModel');

        $game = $this->gameModel->find($id);
        
        if (!$game) {
            $this->setMessage('Gra nie została znaleziona', 'error');
            return redirect()->to('/games');
        }

        $ranges = $this->gameRangeModel->where('game_id', $id)->orderBy('sort_order')->findAll();
        $prizes = $this->gamePrizeModel->where('game_id', $id)->orderBy('numbers_matched')->findAll();

        $data = array_merge($this->viewData, [
            'pageTitle' => 'Edytuj grę: ' . $game['name'],
            'game' => $game,
            'ranges' => $ranges,
            'prizes' => $prizes
        ]);

        return view('games/form', $data);
    }

    /**
     * Aktualizacja gry (UPDATE - save)
     */
    public function update(int $id)
    {
        $this->gameModel = model('GameModel');
        $this->gameRangeModel = model('GameRangeModel');
        $this->gamePrizeModel = model('GamePrizeModel');

        $game = $this->gameModel->find($id);
        
        if (!$game) {
            return $this->jsonResponse(['success' => false, 'message' => 'Gra nie została znaleziona'], 404);
        }

        // Walidacja podobna jak w create(), ale z wykluczeniem aktualnego ID dla slug
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[2]|max_length[100]',
            'slug' => "required|alpha_dash|max_length[50]|is_unique[games.slug,id,{$id}]",
            'bet_price' => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[1000]',
            'api_game_type' => 'permit_empty|max_length[50]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Obsługa logo (zachowaj stare jeśli nie przesłano nowego)
            $logoFilename = $this->handleLogoUpload($game['logo_filename']);

            $gameData = [
                'name' => $this->request->getPost('name'),
                'slug' => $this->request->getPost('slug'),
                'description' => $this->request->getPost('description'),
                'bet_price' => $this->request->getPost('bet_price'),
                'api_game_type' => $this->request->getPost('api_game_type'),
                'logo_filename' => $logoFilename
            ];

            $this->gameModel->update($id, $gameData);

            // Aktualizacja zakresów i nagród
            $this->updateGameRanges($id);
            $this->updateGamePrizes($id);

            $db->transComplete();

            $this->setMessage('Gra została pomyślnie zaktualizowana', 'success');
            return redirect()->to('/games');

        } catch (\Exception $e) {
            $db->transRollback();
            $this->setMessage('Błąd: ' . $e->getMessage(), 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Usuwanie gry (DELETE)
     */
    public function delete(int $id)
    {
        if (!$this->request->is('delete')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Nieprawidłowa metoda'], 405);
        }

        $this->gameModel = model('GameModel');
        $game = $this->gameModel->find($id);
        
        if (!$game) {
            return $this->jsonResponse(['success' => false, 'message' => 'Gra nie została znaleziona'], 404);
        }

        try {
            // Sprawdzenie czy gra ma powiązane dane
            $drawCount = model('DrawModel')->where('game_id', $id)->countAllResults();
            
            if ($drawCount > 0) {
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => "Nie można usunąć gry. Ma przypisane {$drawCount} losowań."
                ], 400);
            }

            // Usuwanie pliku logo
            if ($game['logo_filename']) {
                $logoPath = FCPATH . 'assets/img/' . $game['logo_filename'];
                if (file_exists($logoPath)) {
                    unlink($logoPath);
                }
            }

            $this->gameModel->delete($id);

            return $this->jsonResponse(['success' => true, 'message' => 'Gra została usunięta']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Błąd: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obsługa upload logo gry
     */
    private function handleLogoUpload(?string $currentLogo = null): ?string
{
    $logo = $this->request->getFile('logo');
    
    if (!$logo || !$logo->isValid()) {
        return $currentLogo; // Zachowaj obecne logo
    }

    // Walidacja typu pliku
    $allowedMimes = ['image/jpeg', 'image/png', 'image/svg+xml'];
    if (!in_array($logo->getClientMimeType(), $allowedMimes)) {
        throw new \Exception('Dozwolone formaty logo: JPG, PNG, SVG');
    }

    // Usunięcie starego logo
    if ($currentLogo) {
        $oldPath = FCPATH . 'assets/img/' . $currentLogo;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    // Zapisanie nowego logo
    $newName = $logo->getRandomName();
    $logo->move(FCPATH . 'assets/img/', $newName);

    return $newName;
}

    /**
     * Zapisuje zakresy liczb dla gry
     */
    private function saveGameRanges(int $gameId): void
    {
        $ranges = $this->request->getPost('ranges') ?? [];
        
        foreach ($ranges as $index => $range) {
            if (empty($range['range_name']) || empty($range['max_number'])) {
                continue;
            }

            $rangeData = [
                'game_id' => $gameId,
                'range_name' => $range['range_name'],
                'min_number' => (int)($range['min_number'] ?? 1),
                'max_number' => (int)$range['max_number'],
                'numbers_to_pick' => (int)$range['numbers_to_pick'],
                'is_required' => isset($range['is_required']) ? 1 : 0,
                'sort_order' => $index + 1
            ];

            $this->gameRangeModel->insert($rangeData);
        }
    }

    /**
     * Zapisuje poziomy wygranych dla gry
     */
    private function saveGamePrizes(int $gameId): void
    {
        $prizes = $this->request->getPost('prizes') ?? [];
        
        foreach ($prizes as $prize) {
            if (empty($prize['numbers_matched'])) {
                continue;
            }

            $prizeData = [
                'game_id' => $gameId,
                'range_name' => $prize['range_name'] ?? 'main',
                'numbers_matched' => (int)$prize['numbers_matched'],
                'bonus_matched' => (int)($prize['bonus_matched'] ?? 0),
                'prize_amount' => !empty($prize['prize_amount']) ? (float)$prize['prize_amount'] : null,
                'prize_percentage' => !empty($prize['prize_percentage']) ? (float)$prize['prize_percentage'] : null,
                'prize_description' => $prize['prize_description'] ?? null,
                'is_jackpot' => isset($prize['is_jackpot']) ? 1 : 0
            ];

            $this->gamePrizeModel->insert($prizeData);
        }
    }

    /**
     * Aktualizuje zakresy liczb dla gry
     */
    private function updateGameRanges(int $gameId): void
    {
        // Usuń stare zakresy
        $this->gameRangeModel->where('game_id', $gameId)->delete();
        
        // Dodaj nowe
        $this->saveGameRanges($gameId);
    }

    /**
     * Aktualizuje poziomy wygranych dla gry
     */
    private function updateGamePrizes(int $gameId): void
    {
        // Usuń stare nagrody
        $this->gamePrizeModel->where('game_id', $gameId)->delete();
        
        // Dodaj nowe
        $this->saveGamePrizes($gameId);
    }
}