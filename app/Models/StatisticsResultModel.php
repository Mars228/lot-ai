<?php

// ==========================================
// app/Models/StatisticsResultModel.php (POPRAWIONY)
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class StatisticsResultModel extends Model
{
    protected $table = 'statistics_results';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'model_id', 'draw_number', 'hot_numbers_main', 'hot_numbers_bonus',
        'cold_numbers_main', 'cold_numbers_bonus', 'accuracy_score', 
        'processing_time', 'additional_data'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    /**
     * Pobiera wyniki dla konkretnego modelu z dekodowaniem JSON
     */
    public function getResultsForModel(int $modelId, ?int $limit = null): array
    {
        try {
            $builder = $this->where('model_id', $modelId)
                ->orderBy('draw_number', 'DESC');

            if ($limit && $limit > 0) {
                $builder->limit($limit);
            }

            $results = $builder->findAll();

            // Dekodowanie JSON
            foreach ($results as &$result) {
                $result['hot_numbers_main'] = !empty($result['hot_numbers_main']) ? json_decode($result['hot_numbers_main'], true) : [];
                $result['hot_numbers_bonus'] = !empty($result['hot_numbers_bonus']) ? json_decode($result['hot_numbers_bonus'], true) : null;
                $result['cold_numbers_main'] = !empty($result['cold_numbers_main']) ? json_decode($result['cold_numbers_main'], true) : null;
                $result['cold_numbers_bonus'] = !empty($result['cold_numbers_bonus']) ? json_decode($result['cold_numbers_bonus'], true) : null;
                $result['additional_data'] = !empty($result['additional_data']) ? json_decode($result['additional_data'], true) : null;
            }

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'Błąd w StatisticsResultModel::getResultsForModel: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Pobiera najnowszy wynik dla modelu
     */
    public function getLatestResult(int $modelId): ?array
    {
        try {
            $result = $this->where('model_id', $modelId)
                ->orderBy('draw_number', 'DESC')
                ->first();

            if ($result) {
                $result['hot_numbers_main'] = !empty($result['hot_numbers_main']) ? json_decode($result['hot_numbers_main'], true) : [];
                $result['hot_numbers_bonus'] = !empty($result['hot_numbers_bonus']) ? json_decode($result['hot_numbers_bonus'], true) : null;
                $result['cold_numbers_main'] = !empty($result['cold_numbers_main']) ? json_decode($result['cold_numbers_main'], true) : null;
                $result['cold_numbers_bonus'] = !empty($result['cold_numbers_bonus']) ? json_decode($result['cold_numbers_bonus'], true) : null;
                $result['additional_data'] = !empty($result['additional_data']) ? json_decode($result['additional_data'], true) : null;
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Błąd w StatisticsResultModel::getLatestResult: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Oblicza średnią dokładność modelu
     */
    public function calculateAverageAccuracy(int $modelId, ?int $lastNResults = null): float
    {
        try {
            $builder = $this->select('accuracy_score')
                ->where('model_id', $modelId)
                ->orderBy('draw_number', 'DESC');

            if ($lastNResults && $lastNResults > 0) {
                $builder->limit($lastNResults);
            }

            $results = $builder->findAll();

            if (empty($results)) {
                return 0.0;
            }

            $totalScore = array_sum(array_column($results, 'accuracy_score'));
            return round($totalScore / count($results), 2);
        } catch (\Exception $e) {
            log_message('error', 'Błąd w StatisticsResultModel::calculateAverageAccuracy: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Usuwa stare wyniki (pozostawia najlepsze)
     */
    public function cleanupResults(int $modelId, int $keepBest = 100): int
    {
        try {
            // Znajdź najlepsze wyniki
            $builder = $this->select('id')
                ->where('model_id', $modelId)
                ->orderBy('accuracy_score', 'DESC')
                ->orderBy('draw_number', 'DESC')
                ->limit($keepBest);

            $bestResults = $builder->findAll();

            if (empty($bestResults)) {
                return 0;
            }

            $bestIds = array_column($bestResults, 'id');

            // Usuń pozostałe
            return $this->where('model_id', $modelId)
                ->whereNotIn('id', $bestIds)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'Błąd w StatisticsResultModel::cleanupResults: ' . $e->getMessage());
            return 0;
        }
    }
}