<?php
// ==========================================
// app/Views/games/partials/range_form.php
// ==========================================
?>

<div class="range-form" data-index="<?= $index ?>">
    <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removeRangeForm(this)" title="Usuń zakres">
        <i class="fas fa-times"></i>
    </button>
    
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Nazwa zakresu <span class="text-danger">*</span></label>
                <select class="form-control" name="ranges[<?= $index ?>][range_name]" required>
                    <option value="main" <?= isset($range) && $range['range_name'] === 'main' ? 'selected' : '' ?>>
                        Główny (main)
                    </option>
                    <option value="bonus" <?= isset($range) && $range['range_name'] === 'bonus' ? 'selected' : '' ?>>
                        Bonus
                    </option>
                    <option value="extra" <?= isset($range) && $range['range_name'] === 'extra' ? 'selected' : '' ?>>
                        Dodatkowy (extra)
                    </option>
                </select>
                <small class="form-text text-muted">
                    Typ zakresu liczb (główny, bonus, itp.)
                </small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Od <span class="text-danger">*</span></label>
                <input type="number" 
                       class="form-control" 
                       name="ranges[<?= $index ?>][min_number]" 
                       value="<?= isset($range) ? $range['min_number'] : 1 ?>"
                       min="1" 
                       max="100"
                       required>
                <small class="form-text text-muted">Min. liczba</small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Do <span class="text-danger">*</span></label>
                <input type="number" 
                       class="form-control" 
                       name="ranges[<?= $index ?>][max_number]" 
                       value="<?= isset($range) ? $range['max_number'] : 49 ?>"
                       min="2" 
                       max="100"
                       required>
                <small class="form-text text-muted">Maks. liczba</small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Ile typować <span class="text-danger">*</span></label>
                <input type="number" 
                       class="form-control" 
                       name="ranges[<?= $index ?>][numbers_to_pick]" 
                       value="<?= isset($range) ? $range['numbers_to_pick'] : 6 ?>"
                       min="1" 
                       max="20"
                       required>
                <small class="form-text text-muted">Liczb do wytypowania</small>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="form-group">
                <label>Kolejność</label>
                <input type="number" 
                       class="form-control" 
                       name="ranges[<?= $index ?>][sort_order]" 
                       value="<?= isset($range) ? $range['sort_order'] : $index + 1 ?>"
                       min="1">
                <small class="form-text text-muted">Kolejność wyświetlania</small>
            </div>
        </div>
        
        <div class="col-md-1">
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           name="ranges[<?= $index ?>][is_required]" 
                           id="range_required_<?= $index ?>"
                           value="1"
                           <?= isset($range) && $range['is_required'] ? 'checked' : 'checked' ?>>
                    <label class="form-check-label" for="range_required_<?= $index ?>">
                        Wymagany
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-2">
        <small class="text-muted">
            <strong>Przykład:</strong> 
            Dla Lotto: main, 1-49, typuj 6 | 
            Dla EuroJackpot: main (1-50, typuj 5) + bonus (1-12, typuj 2)
        </small>
    </div>
</div>