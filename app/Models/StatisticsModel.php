<?php

// ==========================================
// app/Models/StatisticsModel.php (POPRAWIONY)
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class StatisticsModel extends Model
{
    protected $table = 'statistics_models';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'game_id', 'model_type', 'model_name', 'parameters', 
        'description', 'is_active', 'accuracy', 'last_processed_draw', 'processing_status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    const MODEL_TYPES = [
        'repeat_count' => 'Model I - Zliczanie powtórzeń',
        'frequent_numbers' => 'Model II - Najczęściej losowane',
        'probabilistic' => 'Model III - Probabilistyka PROB',
        'ensemble' => 'Model IV - ENSEMBLE',
        'ev_optimization' => 'Model V - EV-OPT',
        'gap_analysis' => 'Model VI - Gap-Analysis'
    ];

    /**
     * Pobiera dostępne typy modeli
     */
    public function getModelTypes(): array
    {
        return self::MODEL_TYPES;
    }

    /**
     * Pobiera modele dla konkretnej gry z dekodowaniem JSON
     */
    public function getModelsForGame(int $gameId): array
    {
        try {
            $models = $this->where('game_id', $gameId)
                ->orderBy('model_type')
                ->orderBy('model_name')
                ->findAll();

            // Dekodowanie JSON parametrów
            foreach ($models as &$model) {
                $model['parameters'] = !empty($model['parameters']) ? json_decode($model['parameters'], true) : [];
            }

            return $models;
        } catch (\Exception $e) {
            log_message('error', 'Błąd w StatisticsModel::getModelsForGame: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Pobiera aktywne modele do przetwarzania
     */
    public function getActiveModels(): array
    {
        try {
            return $this->where('is_active', 1)
                ->where('processing_status !=', 'processing')
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Błąd w StatisticsModel::getActiveModels: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Aktualizuje status przetwarzania
     */
    public function updateProcessingStatus(int $modelId, string $status, ?int $lastProcessedDraw = null): bool
    {
        try {
            $data = ['processing_status' => $status];
            
            if ($lastProcessedDraw !== null) {
                $data['last_processed_draw'] = $lastProcessedDraw;
            }

            return $this->update($modelId, $data);
        } catch (\Exception $e) {
            log_message('error', 'Błąd w StatisticsModel::updateProcessingStatus: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Waliduje parametry modelu
     */
    public function validateParameters(string $modelType, array $parameters): array
    {
        $errors = [];

        switch ($modelType) {
            case 'repeat_count':
                if (!isset($parameters['target_numbers']) || $parameters['target_numbers'] < 1) {
                    $errors[] = 'Liczba docelowych liczb musi być większa niż 0';
                }
                if (!isset($parameters['min_repeats']) || $parameters['min_repeats'] < 1) {
                    $errors[] = 'Minimalna liczba powtórzeń musi być większa niż 0';
                }
                break;

            case 'frequent_numbers':
                if (!isset($parameters['top_count']) || $parameters['top_count'] < 1) {
                    $errors[] = 'Liczba wybranych liczb musi być większa niż 0';
                }
                if (!isset($parameters['min_occurrences']) || $parameters['min_occurrences'] < 1) {
                    $errors[] = 'Minimalna liczba wystąpień musi być większa niż 0';
                }
                break;

            case 'probabilistic':
                if (!isset($parameters['window_size']) || $parameters['window_size'] < 10) {
                    $errors[] = 'Rozmiar okna musi być co najmniej 10';
                }
                if (!isset($parameters['alpha']) || $parameters['alpha'] < 0) {
                    $errors[] = 'Parametr alpha musi być nieujemny';
                }
                if (!isset($parameters['beta']) || $parameters['beta'] < 0) {
                    $errors[] = 'Parametr beta musi być nieujemny';
                }
                break;

            case 'ensemble':
                if (!isset($parameters['windows']) || !is_array($parameters['windows'])) {
                    $errors[] = 'Okna muszą być podane jako tablica';
                }
                if (!isset($parameters['weights']) || !is_array($parameters['weights'])) {
                    $errors[] = 'Wagi muszą być podane jako tablica';
                }
                if (isset($parameters['windows']) && isset($parameters['weights']) && 
                    count($parameters['windows']) !== count($parameters['weights'])) {
                    $errors[] = 'Liczba okien musi być równa liczbie wag';
                }
                break;

            case 'ev_optimization':
                if (!isset($parameters['ticket_size']) || $parameters['ticket_size'] < 1) {
                    $errors[] = 'Rozmiar kuponu musi być większy niż 0';
                }
                if (!isset($parameters['hot_range_min']) || $parameters['hot_range_min'] < 1) {
                    $errors[] = 'Minimalny zakres hot musi być większy niż 0';
                }
                if (!isset($parameters['hot_range_max']) || 
                    $parameters['hot_range_max'] < ($parameters['hot_range_min'] ?? 1)) {
                    $errors[] = 'Maksymalny zakres hot musi być większy niż minimum';
                }
                break;

            case 'gap_analysis':
                if (!isset($parameters['window_size']) || $parameters['window_size'] < 10) {
                    $errors[] = 'Rozmiar okna musi być co najmniej 10';
                }
                if (!isset($parameters['percentile']) || $parameters['percentile'] < 0.5 || $parameters['percentile'] > 1) {
                    $errors[] = 'Percentyl musi być między 0.5 a 1.0';
                }
                if (!isset($parameters['top_count']) || $parameters['top_count'] < 1) {
                    $errors[] = 'Liczba wybranych liczb musi być większa niż 0';
                }
                break;
        }

        return $errors;
    }
}