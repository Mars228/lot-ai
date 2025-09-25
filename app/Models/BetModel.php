<?php

// ==========================================
// Poprawiony również BetModel.php
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class BetModel extends Model
{
    protected $table = 'bets';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'package_id', 'bet_number', 'draw_number', 'numbers_main', 
        'numbers_bonus', 'is_random', 'bet_type'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    // USUNIĘTO BŁĘDNE $casts - JSON będzie obsłużony manualnie

    /**
     * Pobiera zakłady dla pakietu
     */
    public function getBetsForPackage(int $packageId): array
    {
        $bets = $this->where('package_id', $packageId)
            ->orderBy('bet_number')
            ->findAll();

        // Dekodowanie JSON
        foreach ($bets as &$bet) {
            $bet['numbers_main'] = json_decode($bet['numbers_main'], true);
            $bet['numbers_bonus'] = $bet['numbers_bonus'] ? json_decode($bet['numbers_bonus'], true) : null;
        }

        return $bets;
    }

    /**
     * Pobiera zakłady dla konkretnego losowania
     */
    public function getBetsForDraw(int $drawNumber): array
    {
        $bets = $this->select('bets.*, bet_packages.game_id, bet_packages.strategy_id')
            ->join('bet_packages', 'bet_packages.id = bets.package_id')
            ->where('bets.draw_number', $drawNumber)
            ->findAll();

        // Dekodowanie JSON
        foreach ($bets as &$bet) {
            $bet['numbers_main'] = json_decode($bet['numbers_main'], true);
            $bet['numbers_bonus'] = $bet['numbers_bonus'] ? json_decode($bet['numbers_bonus'], true) : null;
        }

        return $bets;
    }

    /**
     * Sprawdza trafienia w zakładzie
     */
    public function checkMatches(array $betNumbers, array $drawNumbers): int
    {
        return count(array_intersect($betNumbers, $drawNumbers));
    }
}