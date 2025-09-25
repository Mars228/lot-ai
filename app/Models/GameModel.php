<?php

// ==========================================
// app/Models/GameModel.php
// ==========================================


namespace App\Models;

use CodeIgniter\Model;

class GameModel extends Model
{
    protected $table = 'games';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'slug', 'description', 'bet_price', 
        'logo_filename', 'api_game_type', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Pobierz grę po slug
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Pobierz aktywne gry
     */
    public function getActiveGames()
    {
        return $this->where('is_active', 1)->findAll();
    }
}