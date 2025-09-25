<?php

// ==========================================
// app/Models/GamePrizeModel.php
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class GamePrizeModel extends Model
{
    protected $table = 'game_prizes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'game_id', 'range_name', 'numbers_selected', 'numbers_matched', 
        'bonus_matched', 'prize_amount', 'prize_percentage', 
        'prize_description', 'is_jackpot'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Pobierz wygrane dla konkretnego wariantu gry
     */
    public function getPrizesForVariant($gameId, $numbersSelected)
    {
        return $this->where('game_id', $gameId)
                    ->where('numbers_selected', $numbersSelected)
                    ->orderBy('numbers_matched', 'DESC')
                    ->findAll();
    }

    /**
     * Pobierz wszystkie warianty wygranych dla gry
     */
    public function getAllPrizesGroupedByVariant($gameId)
    {
        $prizes = $this->where('game_id', $gameId)
                       ->orderBy('numbers_selected', 'ASC')
                       ->orderBy('numbers_matched', 'DESC')
                       ->findAll();
        
        $grouped = [];
        foreach ($prizes as $prize) {
            $variant = $prize['numbers_selected'] ?? 'default';
            $grouped[$variant][] = $prize;
        }
        
        return $grouped;
    }

    /**
     * Sprawdź wygraną dla podanych parametrów
     */
    public function checkPrize($gameId, $numbersSelected, $numbersMatched, $bonusMatched = 0)
    {
        $builder = $this->where('game_id', $gameId)
                        ->where('numbers_matched', $numbersMatched);
        
        if ($numbersSelected !== null) {
            $builder->where('numbers_selected', $numbersSelected);
        }
        
        if ($bonusMatched > 0) {
            $builder->where('bonus_matched', $bonusMatched);
        }
        
        return $builder->first();
    }

    /**
     * Zapisz lub zaktualizuj wygraną
     */
    public function savePrize($data)
    {
        // Sprawdź czy istnieje
        $existing = $this->where('game_id', $data['game_id'])
                         ->where('numbers_selected', $data['numbers_selected'])
                         ->where('numbers_matched', $data['numbers_matched'])
                         ->where('range_name', $data['range_name'] ?? 'main')
                         ->first();
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }
}