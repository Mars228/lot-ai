<?php

// ==========================================
// app/Models/SettingsModel.php (POPRAWIONY)
// ==========================================

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['setting_key', 'setting_value', 'description'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Pobiera wartość ustawienia
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $setting = $this->where('setting_key', $key)->first();
            return $setting ? $setting['setting_value'] : $default;
        } catch (\Exception $e) {
            log_message('error', 'Błąd w SettingsModel::getValue: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Ustawia wartość ustawienia
     */
    public function setValue(string $key, mixed $value, ?string $description = null): bool
    {
        try {
            $existing = $this->where('setting_key', $key)->first();
            
            if ($existing) {
                return $this->update($existing['id'], ['setting_value' => $value]);
            } else {
                return $this->insert([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'description' => $description
                ]) !== false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Błąd w SettingsModel::setValue: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pobiera wszystkie ustawienia jako tablicę klucz-wartość
     */
    public function getAllSettings(): array
    {
        try {
            $settings = $this->findAll();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Błąd w SettingsModel::getAllSettings: ' . $e->getMessage());
            return [];
        }
    }
}