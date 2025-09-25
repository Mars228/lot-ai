<!-- ======================================== -->
<!-- 3. VIEW: app/Views/games/prizes/form.php -->
<!-- Formularz dodawania/edycji wygranej -->
<!-- ======================================== -->

<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <a href="/games/<?= $game['id'] ?>/prizes" class="btn btn-link p-0">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <?= $prize ? 'Edytuj wygraną' : 'Dodaj wygraną' ?> - <?= esc($game['name']) ?>
                    </h3>
                </div>
                
                <form action="/games/<?= $game['id'] ?>/prizes/save" method="post">
                    <?= csrf_field() ?>
                    <?php if ($prize): ?>
                        <input type="hidden" name="prize_id" value="<?= $prize['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Wariant gry (dla Multi Multi) -->
                        <?php if (!empty($variants)): ?>
                        <div class="form-group">
                            <label for="numbers_selected">Wariant gry <span class="text-danger">*</span></label>
                            <select name="numbers_selected" id="numbers_selected" class="form-control" required>
                                <option value="">-- Wybierz wariant --</option>
                                <?php foreach ($variants as $variant): ?>
                                <option value="<?= $variant['numbers_to_select'] ?>" 
                                        <?= ($prize && $prize['numbers_selected'] == $variant['numbers_to_select']) ? 'selected' : '' ?>>
                                    <?= esc($variant['variant_name']) ?> (typowanie <?= $variant['numbers_to_select'] ?> liczb)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                            <input type="hidden" name="numbers_selected" value="<?= $prize['numbers_selected'] ?? '' ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numbers_matched">Trafione liczby <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="numbers_matched" 
                                           id="numbers_matched" 
                                           class="form-control" 
                                           value="<?= old('numbers_matched', $prize['numbers_matched'] ?? '') ?>"
                                           min="0" 
                                           max="10" 
                                           required>
                                    <small class="form-text text-muted">Ile liczb gracz musi trafić</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bonus_matched">Liczby bonus (opcjonalne)</label>
                                    <input type="number" 
                                           name="bonus_matched" 
                                           id="bonus_matched" 
                                           class="form-control" 
                                           value="<?= old('bonus_matched', $prize['bonus_matched'] ?? 0) ?>"
                                           min="0" 
                                           max="2">
                                    <small class="form-text text-muted">Dla gier z dodatkowymi liczbami</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prize_amount">Kwota wygranej (zł) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="prize_amount" 
                                           id="prize_amount" 
                                           class="form-control" 
                                           value="<?= old('prize_amount', $prize['prize_amount'] ?? '') ?>"
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prize_percentage">Procent puli (opcjonalne)</label>
                                    <input type="number" 
                                           name="prize_percentage" 
                                           id="prize_percentage" 
                                           class="form-control" 
                                           value="<?= old('prize_percentage', $prize['prize_percentage'] ?? '') ?>"
                                           step="0.01" 
                                           min="0" 
                                           max="100">
                                    <small class="form-text text-muted">Dla wygranych zależnych od puli</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prize_description">Opis wygranej <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="prize_description" 
                                   id="prize_description" 
                                   class="form-control" 
                                   value="<?= old('prize_description', $prize['prize_description'] ?? '') ?>"
                                   placeholder="np. Trafienie 3 z 5"
                                   maxlength="255" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="range_name">Zakres liczb</label>
                            <input type="text" 
                                   name="range_name" 
                                   id="range_name" 
                                   class="form-control" 
                                   value="<?= old('range_name', $prize['range_name'] ?? 'main') ?>">
                            <small class="form-text text-muted">Domyślnie: main (główny zakres liczb)</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_jackpot" 
                                       name="is_jackpot" 
                                       value="1"
                                       <?= ($prize && $prize['is_jackpot']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_jackpot">
                                    <i class="fas fa-crown text-warning"></i> Oznacz jako główną wygraną (jackpot)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Zapisz
                        </button>
                        <a href="/games/<?= $game['id'] ?>/prizes" class="btn btn-default">
                            <i class="fas fa-times"></i> Anuluj
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i> Podpowiedzi
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Dla gry <?= esc($game['name']) ?>:</h6>
                    <ul class="small">
                        <?php if ($game['slug'] === 'multi-multi'): ?>
                        <li>Wybierz wariant gry (od 1 do 10 typowanych liczb)</li>
                        <li>Liczba trafionych nie może być większa niż liczba typowanych</li>
                        <li>Kwoty wygranych rosną wykładniczo z ilością trafionych liczb</li>
                        <li>Wariant 10/80 ma specjalną wygraną za 0 trafień</li>
                        <li>Najwyższa wygrana to 1 000 000 zł za trafienie 10 z 10</li>
                        <?php else: ?>
                        <li>Wprowadź ilość trafionych liczb głównych</li>
                        <li>Opcjonalnie dodaj liczby bonus (jeśli gra je posiada)</li>
                        <li>Możesz ustawić stałą kwotę lub procent puli</li>
                        <li>Jackpot to zwykle najwyższa wygrana w grze</li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if ($game['slug'] === 'multi-multi' && !$prize): ?>
                    <div class="alert alert-info mt-3">
                        <strong>Wskazówka:</strong><br>
                        Możesz wrócić do listy wygranych i użyć przycisku "Import Multi Multi" aby automatycznie dodać wszystkie wygrane.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Walidacja dla Multi Multi
    $('#numbers_selected, #numbers_matched').on('change', function() {
        var selected = parseInt($('#numbers_selected').val());
        var matched = parseInt($('#numbers_matched').val());
        
        if (selected && matched) {
            if (matched > selected) {
                alert('Liczba trafionych nie może być większa niż liczba typowanych!');
                $('#numbers_matched').val('');
            }
        }
    });
    
    // Auto-generowanie opisu
    $('#numbers_selected, #numbers_matched').on('blur', function() {
        var selected = $('#numbers_selected').val();
        var matched = $('#numbers_matched').val();
        
        if (selected && matched && !$('#prize_description').val()) {
            $('#prize_description').val('Trafienie ' + matched + ' z ' + selected);
        }
    });
});
</script>
<?= $this->endSection() ?>