<?php

// ========================================
// 2. CONTROLLER: app/Controllers/GameController.php
// ========================================

namespace App\Controllers;

use App\Models\GameModel;
use App\Models\GamePrizeModel;
use App\Models\GameVariantModel;
use App\Models\GameRangeModel;

class GameController extends BaseController
{
    protected $gameModel;
    protected $prizeModel;
    protected $variantModel;
    protected $rangeModel;
    
    public function __construct()
    {
        $this->gameModel = new GameModel();
        $this->prizeModel = new GamePrizeModel();
        $this->variantModel = new GameVariantModel();
        $this->rangeModel = new GameRangeModel();
    }

    /**
     * Lista wszystkich gier
     */
    public function index()
    {
        $data = [
            'title' => 'Gry liczbowe',
            'games' => $this->gameModel->getActiveGames()
        ];

        return view('games/index', $data);
    }

    /**
     * Wyświetl szczegóły gry
     */
    public function show($slug)
    {
        $game = $this->gameModel->getBySlug($slug);
        
        if (!$game) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => $game['name'],
            'game' => $game,
            'variants' => $this->variantModel->getVariantsByGameId($game['id']),
            'prizes' => $this->prizeModel->getAllPrizesGroupedByVariant($game['id']),
            'ranges' => $this->rangeModel->where('game_id', $game['id'])->findAll()
        ];

        // Dla Multi Multi użyj specjalnego widoku
        if ($slug === 'multi-multi') {
            return view('games/multi_multi', $data);
        }

        return view('games/show', $data);
    }

    /**
     * Formularz dodawania/edycji gry
     */
    public function form($id = null)
    {
        $game = null;
        if ($id) {
            $game = $this->gameModel->find($id);
        }

        $data = [
            'title' => $game ? 'Edytuj grę' : 'Dodaj grę',
            'game' => $game,
            'validation' => \Config\Services::validation()
        ];

        return view('games/form', $data);
    }

    /**
     * Zapisz grę
     */
    public function save()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'slug' => 'required|alpha_dash|max_length[50]',
            'bet_price' => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
            'bet_price' => $this->request->getPost('bet_price'),
            'api_game_type' => $this->request->getPost('api_game_type'),
            'logo_filename' => $this->request->getPost('logo_filename'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        $gameId = $this->request->getPost('game_id');
        
        if ($gameId) {
            $this->gameModel->update($gameId, $data);
            $message = 'Gra została zaktualizowana';
        } else {
            $this->gameModel->insert($data);
            $gameId = $this->gameModel->insertID();
            $message = 'Gra została dodana';
        }

        return redirect()->to('/games/' . $data['slug'])
                         ->with('success', $message);
    }

    /**
     * API endpoint - pobierz wygrane dla wariantu
     */
    public function getPrizesForVariant($gameId, $numbersSelected)
    {
        $prizes = $this->prizeModel->getPrizesForVariant($gameId, $numbersSelected);
        
        return $this->response->setJSON([
            'success' => true,
            'prizes' => $prizes
        ]);
    }

    /**
     * Kalkulator wygranych
     */
    public function calculatePrize()
    {
        $gameId = $this->request->getPost('game_id');
        $numbersSelected = $this->request->getPost('numbers_selected');
        $numbersMatched = $this->request->getPost('numbers_matched');
        
        $prize = $this->prizeModel->checkPrize($gameId, $numbersSelected, $numbersMatched);
        
        if ($prize) {
            return $this->response->setJSON([
                'success' => true,
                'prize' => $prize,
                'message' => sprintf('Wygrana: %s zł - %s', 
                    number_format($prize['prize_amount'], 2), 
                    $prize['prize_description']
                )
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Brak wygranej dla tej kombinacji'
            ]);
        }
    }
}