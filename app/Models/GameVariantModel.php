<?php

// ========================================
// 3. MODEL: app/Models/GameVariantModel.php
// ========================================

namespace App\Models;

use CodeIgniter\Model;

class GameVariantModel extends Model
{
    protected $table = 'game_variants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'game_id', 'variant_name', 'numbers_to_select', 'multiplier', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Pobierz warianty dla gry
     */
    public function getVariantsByGameId($gameId)
    {
        return $this->where('game_id', $gameId)
                    ->where('is_active', 1)
                    ->orderBy('numbers_to_select', 'ASC')
                    ->findAll();
    }

    /**
     * Pobierz warianty z ilością wygranych
     */
    public function getVariantsWithPrizeCounts($gameId)
    {
        return $this->db->table($this->table . ' gv')
            ->select('gv.*, COUNT(gp.id) as prize_levels, MAX(gp.prize_amount) as max_prize')
            ->join('game_prizes gp', 'gv.game_id = gp.game_id AND gv.numbers_to_select = gp.numbers_selected', 'left')
            ->where('gv.game_id', $gameId)
            ->groupBy('gv.id')
            ->orderBy('gv.numbers_to_select', 'ASC')
            ->get()
            ->getResultArray();
    }
}