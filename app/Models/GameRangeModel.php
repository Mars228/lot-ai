<?php

// ==========================================
// app/Models/GameRangeModel.php
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class GameRangeModel extends Model
{
    protected $table = 'game_ranges';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'game_id', 'range_name', 'min_number', 'max_number', 
        'numbers_to_pick', 'is_required', 'sort_order'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Pobierz zakresy dla gry
     */
    public function getRangesByGameId($gameId)
    {
        return $this->where('game_id', $gameId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
}