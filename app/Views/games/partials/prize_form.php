<?php
// ==========================================
// app/Views/games/partials/prize_form.php
// ==========================================
?>

<div class="prize-form" data-index="<?= $index ?>">
    <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removePrizeForm(this)" title="Usuń poziom wygranej">
        <i class="fas fa-times"></i>
    </button>
    
    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label>Zakres</label>
                <select class="form-control" name="prizes[<?= $index ?>][range_name]">
                    <option value="main" <?= isset($prize) && $prize['range_name'] === 'main' ? 'selected' : '' ?>>
                        Główny
                    </option>
                    <option value="bonus" <?= isset($prize) && $prize['range_name'] === 'bonus' ? 'selected' : '' ?>>
                        Bonus
                    </option>
                    <option value="both" <?= isset($prize) && $prize['range_name'] === 'both' ? 'selected' : '' ?>>
                        Główny + Bonus
                    </option>
                </select>
                <small class="form-text text-muted">Którego zakresu dotyczy</small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Trafień głównych <span class="text-danger">*</span></label>
                <input type="number" 
                       class="form-control" 
                       name="prizes[<?= $index ?>][numbers_matched]" 
                       value="<?= isset($prize) ? $prize['numbers_matched'] : 3 ?>"
                       min="0" 
                       max="20"
                       required>
                <small class="form-text text-muted">Ile liczb głównych</small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Trafień bonus</label>
                <input type="number" 
                       class="form-control" 
                       name="prizes[<?= $index ?>][bonus_matched]" 
                       value="<?= isset($prize) ? $prize['bonus_matched'] : 0 ?>"
                       min="0" 
                       max="10">
                <small class="form-text text-muted">Ile liczb bonus</small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Kwota (zł)</label>
                <input type="number" 
                       class="form-control" 
                       name="prizes[<?= $index ?>][prize_amount]" 
                       value="<?= isset($prize) ? $prize['prize_amount'] : '' ?>"
                       step="0.01"
                       min="0">
                <small class="form-text text-muted">Stała kwota wygranej</small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Procent (%)</label>
                <input type="number" 
                       class="form-control" 
                       name="prizes[<?= $index ?>][prize_percentage]" 
                       value="<?= isset($prize) ? $prize['prize_percentage'] : '' ?>"
                       step="0.01"
                       min="0"
                       max="100">
                <small class="form-text text-muted">% puli nagród</small>
            </div>
        </div>
        
        <div class="col-md-1">
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           name="prizes[<?= $index ?>][is_jackpot]" 
                           id="prize_jackpot_<?= $index ?>"
                           value="1"
                           <?= isset($prize) && $prize['is_jackpot'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="prize_jackpot_<?= $index ?>">
                        Jackpot
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-md-1">
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="text-center">
                    <?php if (isset($prize) && $prize['is_jackpot']): ?>
                        <i class="fas fa-crown text-warning fa-lg" title="Nagroda główna"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Opis wygranej</label>
                <input type="text" 
                       class="form-control" 
                       name="prizes[<?= $index ?>][prize_description]" 
                       value="<?= isset($prize) ? esc($prize['prize_description']) : '' ?>"
                       placeholder="np. Trafienie 3 liczb, Nagroda III stopnia">
                <small class="form-text text-muted">
                    Opcjonalny opis wygranej dla użytkowników
                </small>
            </div>
        </div>
    </div>
    
    <div class="mt-2">
        <small class="text-muted">
            <strong>Uwaga:</strong> Podaj ALBO kwotę stałą ALBO procent puli. 
            Dla jackpotów zazwyczaj używa się procentów lub pozostawiasz puste (zmienna kumulacja).
        </small>
    </div>
</div>