<?php

// ==========================================
// app/Controllers/Home.php - BEZPIECZNA WERSJA
// ==========================================

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Podstawowe dane bez dostępu do bazy danych na początku
        $latestDraws = [];
        $weeklyWins = [];
        $monthlyWins = [];
        $systemInfo = [
            'database_size' => 'N/A',
            'last_update' => null,
            'total_draws' => 0,
            'total_bets' => 0,
            'winning_bets' => 0
        ];

        // Próbujemy załadować dane z bazy tylko jeśli tabele istnieją
        try {
            // Sprawdzamy czy tabele istnieją
            $db = \Config\Database::connect();
            if ($db->tableExists('games')) {
                $gameModel = model('GameModel');
                $games = $gameModel->getActiveGamesForSelect();
                
                if (!empty($games) && $db->tableExists('draws')) {
                    $drawModel = model('DrawModel');
                    
                    foreach ($games as $gameId => $gameName) {
                        $latestDraw = $drawModel->getLatestDrawForGame($gameId);
                        if ($latestDraw) {
                            $latestDraws[] = [
                                'game' => ['id' => $gameId, 'name' => $gameName],
                                'draw' => $latestDraw
                            ];
                        }
                    }
                }

                // Aktualizuj informacje systemowe
                $systemInfo['database_size'] = $this->getDatabaseSize();
                if ($db->tableExists('draws')) {
                    $systemInfo['total_draws'] = $drawModel->countAll();
                }
            }

        } catch (\Throwable $e) {
            log_message('error', 'Błąd w Home::index(): ' . $e->getMessage());
            // Kontynuuj z pustymi danymi
        }

        $data = array_merge($this->viewData, [
            'pageTitle' => 'Strona główna - Analizator Gier Liczbowych',
            'latestDraws' => $latestDraws,
            'weeklyWins' => $weeklyWins,
            'monthlyWins' => $monthlyWins,
            'systemInfo' => $systemInfo
        ]);

        return view('home/index', $data);
    }

    /**
     * Pobiera rozmiar bazy danych
     */
    private function getDatabaseSize(): string
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            
            $result = $query->getRow();
            return $result && $result->size_mb ? $result->size_mb . ' MB' : 'N/A';
            
        } catch (\Throwable $e) {
            log_message('error', 'Błąd pobierania rozmiaru bazy: ' . $e->getMessage());
            return 'N/A';
        }
    }
}