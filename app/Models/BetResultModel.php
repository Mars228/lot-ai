<?php

// ==========================================
// app/Models/BetResultModel.php
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class BetResultModel extends Model
{
    protected $table = 'bet_results';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'bet_id', 'draw_id', 'numbers_matched_main', 'numbers_matched_bonus',
        'prize_amount', 'prize_level', 'roi_percentage'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    /**
     * Pobiera wygrane z ostatniego tygodnia
     */
    public function getWeeklyWins(): array
    {
        $weekAgo = date('Y-m-d H:i:s', strtotime('-1 week'));
        
        return $this->select('bet_results.*, games.name as game_name')
            ->join('bets', 'bets.id = bet_results.bet_id')
            ->join('bet_packages', 'bet_packages.id = bets.package_id')
            ->join('games', 'games.id = bet_packages.game_id')
            ->where('bet_results.created_at >=', $weekAgo)
            ->orderBy('bet_results.prize_amount', 'DESC')
            ->findAll();
    }

    /**
     * Pobiera wygrane z ostatniego miesiąca
     */
    public function getMonthlyWins(): array
    {
        $monthAgo = date('Y-m-d H:i:s', strtotime('-1 month'));
        
        return $this->select('bet_results.*, games.name as game_name')
            ->join('bets', 'bets.id = bet_results.bet_id')
            ->join('bet_packages', 'bet_packages.id = bets.package_id')
            ->join('games', 'games.id = bet_packages.game_id')
            ->where('bet_results.created_at >=', $monthAgo)
            ->orderBy('bet_results.prize_amount', 'DESC')
            ->findAll();
    }

    /**
     * Pobiera statystyki wygranych dla gry
     */
    public function getWinStatsForGame(int $gameId, string $period = 'month'): array
    {
        $dateFrom = match($period) {
            'week' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'month' => date('Y-m-d H:i:s', strtotime('-1 month')),
            'year' => date('Y-m-d H:i:s', strtotime('-1 year')),
            default => date('Y-m-d H:i:s', strtotime('-1 month'))
        };

        return $this->select('
                COUNT(*) as total_wins,
                SUM(prize_amount) as total_amount,
                AVG(prize_amount) as avg_amount,
                MAX(prize_amount) as max_amount
            ')
            ->join('bets', 'bets.id = bet_results.bet_id')
            ->join('bet_packages', 'bet_packages.id = bets.package_id')
            ->where('bet_packages.game_id', $gameId)
            ->where('bet_results.created_at >=', $dateFrom)
            ->first();
    }
}