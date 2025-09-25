<?php
// ========================================
// 1. CONTROLLER: app/Controllers/GamePrizesController.php
// ========================================

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GameModel;
use App\Models\GamePrizeModel;
use App\Models\GameVariantModel;

class GamePrizesController extends BaseController
{
    protected $gameModel;
    protected $prizeModel;
    protected $variantModel;
    
    public function __construct()
    {
        $this->gameModel = new GameModel();
        $this->prizeModel = new GamePrizeModel();
        $this->variantModel = new GameVariantModel();
    }

    /**
     * Lista wygranych dla gry
     */
    public function index($gameId)
    {
        $game = $this->gameModel->find($gameId);
        
        if (!$game) {
            return redirect()->to('/games')->with('error', 'Gra nie została znaleziona');
        }

        $data = [
            'title' => 'Wygrane - ' . $game['name'],
            'game' => $game,
            'variants' => $this->variantModel->getVariantsWithPrizeCounts($gameId),
            'prizes' => $this->prizeModel->getAllPrizesGroupedByVariant($gameId)
        ];

        return view('games/prizes/index', $data);
    }

    /**
     * Formularz dodawania/edycji wygranej
     */
    public function form($gameId, $prizeId = null)
    {
        $game = $this->gameModel->find($gameId);
        
        if (!$game) {
            return redirect()->to('/games')->with('error', 'Gra nie została znaleziona');
        }

        $prize = null;
        if ($prizeId) {
            $prize = $this->prizeModel->find($prizeId);
        }

        $data = [
            'title' => $prize ? 'Edytuj wygraną' : 'Dodaj wygraną',
            'game' => $game,
            'prize' => $prize,
            'variants' => $this->variantModel->getVariantsByGameId($gameId),
            'validation' => \Config\Services::validation()
        ];

        return view('games/prizes/form', $data);
    }

    /**
     * Zapisz wygraną
     */
    public function save($gameId)
    {
        $game = $this->gameModel->find($gameId);
        
        if (!$game) {
            return redirect()->to('/games')->with('error', 'Gra nie została znaleziona');
        }

        $rules = [
            'numbers_selected' => 'required|integer|greater_than[0]|less_than_equal_to[10]',
            'numbers_matched' => 'required|integer|greater_than_equal_to[0]',
            'prize_amount' => 'required|decimal|greater_than_equal_to[0]',
            'prize_description' => 'required|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'game_id' => $gameId,
            'range_name' => $this->request->getPost('range_name') ?? 'main',
            'numbers_selected' => $this->request->getPost('numbers_selected'),
            'numbers_matched' => $this->request->getPost('numbers_matched'),
            'bonus_matched' => $this->request->getPost('bonus_matched') ?? 0,
            'prize_amount' => $this->request->getPost('prize_amount'),
            'prize_percentage' => $this->request->getPost('prize_percentage'),
            'prize_description' => $this->request->getPost('prize_description'),
            'is_jackpot' => $this->request->getPost('is_jackpot') ? 1 : 0
        ];

        $prizeId = $this->request->getPost('prize_id');
        
        if ($prizeId) {
            $this->prizeModel->update($prizeId, $data);
            $message = 'Wygrana została zaktualizowana';
        } else {
            $this->prizeModel->insert($data);
            $message = 'Wygrana została dodana';
        }

        return redirect()->to('/games/' . $gameId . '/prizes')
                         ->with('success', $message);
    }

    /**
     * Usuń wygraną
     */
    public function delete($gameId, $prizeId)
    {
        $prize = $this->prizeModel->find($prizeId);
        
        if (!$prize || $prize['game_id'] != $gameId) {
            return redirect()->back()->with('error', 'Wygrana nie została znaleziona');
        }

        $this->prizeModel->delete($prizeId);
        
        return redirect()->to('/games/' . $gameId . '/prizes')
                         ->with('success', 'Wygrana została usunięta');
    }

    /**
     * Import wygranych dla Multi Multi
     */
    public function importMultiMultiPrizes($gameId)
    {
        $game = $this->gameModel->find($gameId);
        
        if (!$game || $game['slug'] !== 'multi-multi') {
            return redirect()->back()->with('error', 'Ta funkcja jest dostępna tylko dla gry Multi Multi');
        }

        // Struktura wygranych Multi Multi
        $prizesStructure = [
            1 => [
                [1, 4.00, 'Trafienie 1 z 1']
            ],
            2 => [
                [2, 16.00, 'Trafienie 2 z 2']
            ],
            3 => [
                [3, 64.00, 'Trafienie 3 z 3'],
                [2, 2.00, 'Trafienie 2 z 3']
            ],
            4 => [
                [4, 256.00, 'Trafienie 4 z 4'],
                [3, 8.00, 'Trafienie 3 z 4'],
                [2, 2.00, 'Trafienie 2 z 4']
            ],
            5 => [
                [5, 1024.00, 'Trafienie 5 z 5'],
                [4, 32.00, 'Trafienie 4 z 5'],
                [3, 4.00, 'Trafienie 3 z 5']
            ],
            6 => [
                [6, 4096.00, 'Trafienie 6 z 6'],
                [5, 128.00, 'Trafienie 5 z 6'],
                [4, 8.00, 'Trafienie 4 z 6'],
                [3, 2.00, 'Trafienie 3 z 6']
            ],
            7 => [
                [7, 16384.00, 'Trafienie 7 z 7'],
                [6, 512.00, 'Trafienie 6 z 7'],
                [5, 32.00, 'Trafienie 5 z 7'],
                [4, 4.00, 'Trafienie 4 z 7']
            ],
            8 => [
                [8, 65536.00, 'Trafienie 8 z 8'],
                [7, 2048.00, 'Trafienie 7 z 8'],
                [6, 128.00, 'Trafienie 6 z 8'],
                [5, 16.00, 'Trafienie 5 z 8'],
                [4, 2.00, 'Trafienie 4 z 8']
            ],
            9 => [
                [9, 262144.00, 'Trafienie 9 z 9'],
                [8, 8192.00, 'Trafienie 8 z 9'],
                [7, 512.00, 'Trafienie 7 z 9'],
                [6, 64.00, 'Trafienie 6 z 9'],
                [5, 8.00, 'Trafienie 5 z 9'],
                [4, 2.00, 'Trafienie 4 z 9']
            ],
            10 => [
                [10, 1000000.00, 'Trafienie 10 z 10 - GŁÓWNA WYGRANA!', true],
                [9, 32768.00, 'Trafienie 9 z 10'],
                [8, 2048.00, 'Trafienie 8 z 10'],
                [7, 256.00, 'Trafienie 7 z 10'],
                [6, 32.00, 'Trafienie 6 z 10'],
                [5, 4.00, 'Trafienie 5 z 10'],
                [0, 4.00, 'Trafienie 0 z 10 - wygrana pocieszenia']
            ]
        ];

        // Najpierw utwórz warianty gry
        for ($i = 1; $i <= 10; $i++) {
            $variantData = [
                'game_id' => $gameId,
                'variant_name' => "Multi Multi $i/80",
                'numbers_to_select' => $i,
                'multiplier' => 1.00,
                'is_active' => 1
            ];
            
            // Sprawdź czy wariant już istnieje
            $existing = $this->variantModel->where('game_id', $gameId)
                                          ->where('numbers_to_select', $i)
                                          ->first();
            
            if (!$existing) {
                $this->variantModel->insert($variantData);
            }
        }

        // Import wygranych
        $imported = 0;
        foreach ($prizesStructure as $numbersSelected => $prizes) {
            foreach ($prizes as $prize) {
                $data = [
                    'game_id' => $gameId,
                    'range_name' => 'main',
                    'numbers_selected' => $numbersSelected,
                    'numbers_matched' => $prize[0],
                    'prize_amount' => $prize[1],
                    'prize_description' => $prize[2],
                    'is_jackpot' => $prize[3] ?? false
                ];
                
                $this->prizeModel->savePrize($data);
                $imported++;
            }
        }

        return redirect()->to('/games/' . $gameId . '/prizes')
                         ->with('success', "Zaimportowano $imported wygranych dla Multi Multi");
    }
}