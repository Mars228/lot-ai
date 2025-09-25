<?php
// ==========================================
// app/Views/games/form.php
// ==========================================
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?= $game ? 'Edytuj grę' : 'Dodaj nową grę' ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('/games') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Powrót do listy
                    </a>
                </div>
            </div>
            
            <?= form_open_multipart($game ? '/games/update/' . $game['id'] : '/games/create', ['id' => 'gameForm']) ?>
            <div class="card-body">
                
                <!-- Podstawowe informacje -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nazwa gry <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name', $game['name'] ?? '') ?>"
                                   required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Nazwa wyświetlana w aplikacji, np. "Lotto", "Multi Multi"
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="slug">Slug URL <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" 
                                   id="slug" 
                                   name="slug" 
                                   value="<?= old('slug', $game['slug'] ?? '') ?>"
                                   pattern="[a-z0-9-]+"
                                   required>
                            <?php if (isset($errors['slug'])): ?>
                                <div class="invalid-feedback"><?= $errors['slug'] ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Tylko małe litery, cyfry i myślniki, np. "lotto", "multi-multi"
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bet_price">Cena zakładu (zł) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control <?= isset($errors['bet_price']) ? 'is-invalid' : '' ?>" 
                                       id="bet_price" 
                                       name="bet_price" 
                                       value="<?= old('bet_price', $game['bet_price'] ?? '') ?>"
                                       step="0.01"
                                       min="0.01"
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text">zł</span>
                                </div>
                                <?php if (isset($errors['bet_price'])): ?>
                                    <div class="invalid-feedback"><?= $errors['bet_price'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="api_game_type">Typ gry w API</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="api_game_type" 
                                   name="api_game_type" 
                                   value="<?= old('api_game_type', $game['api_game_type'] ?? '') ?>">
                            <small class="form-text text-muted">
                                Nazwa gry w Lotto OpenAPI, np. "Lotto", "MultiMulti", "EuroJackpot"
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Opis zasad gry</label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              rows="3"><?= old('description', $game['description'] ?? '') ?></textarea>
                    <small class="form-text text-muted">
                        Krótki opis jak się gra, np. "Typujemy 6 liczb z 49"
                    </small>
                </div>

                <div class="form-group">
                    <label for="logo">Logo gry</label>
                    <div class="custom-file">
                        <input type="file" 
                               class="custom-file-input" 
                               id="logo" 
                               name="logo"
                               accept="image/jpeg,image/png,image/svg+xml">
                        <label class="custom-file-label" for="logo">Wybierz plik...</label>
                    </div>
                    <small class="form-text text-muted">
                        Dozwolone formaty: JPG, PNG, SVG. Maksymalny rozmiar: 2MB
                    </small>
                    <?php if ($game && $game['logo_filename']): ?>
                        <div class="mt-2">
                            <img src="<?= base_url('assets/img/' . $game['logo_filename']) ?>" 
                                 alt="Aktualne logo" 
                                 class="img-thumbnail" 
                                 style="max-width: 100px; max-height: 100px;">
                            <br>
                            <small class="text-muted">Aktualne logo: <?= esc($game['logo_filename']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Zakresy liczb -->
                <hr>
                <h5>Zakresy liczb</h5>
                <div id="ranges-container">
                    <?php if (!empty($ranges)): ?>
                        <?php foreach ($ranges as $index => $range): ?>
                            <?= view('games/partials/range_form', ['range' => $range, 'index' => $index]) ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?= view('games/partials/range_form', ['range' => null, 'index' => 0]) ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRangeForm()">
                    <i class="fas fa-plus mr-1"></i>
                    Dodaj zakres
                </button>

                <!-- Poziomy wygranych -->
                <hr>
                <h5>Poziomy wygranych</h5>
                <div id="prizes-container">
                    <?php if (!empty($prizes)): ?>
                        <?php foreach ($prizes as $index => $prize): ?>
                            <?= view('games/partials/prize_form', ['prize' => $prize, 'index' => $index]) ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?= view('games/partials/prize_form', ['prize' => null, 'index' => 0]) ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="addPrizeForm()">
                    <i class="fas fa-plus mr-1"></i>
                    Dodaj poziom wygranej
                </button>
                
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    <?= $game ? 'Zaktualizuj grę' : 'Zapisz grę' ?>
                </button>
                <a href="<?= base_url('/games') ?>" class="btn btn-secondary ml-2">
                    <i class="fas fa-times mr-2"></i>
                    Anuluj
                </a>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto-generowanie slug z nazwy
$('#name').on('input', function() {
    if (!$('#slug').val()) {
        let slug = $(this).val()
            .toLowerCase()
            .replace(/[ąćęłńóśźż]/g, function(match) {
                const map = {'ą':'a','ć':'c','ę':'e','ł':'l','ń':'n','ó':'o','ś':'s','ź':'z','ż':'z'};
                return map[match];
            })
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        $('#slug').val(slug);
    }
});

// Aktualizacja nazwy pliku w custom-file-input
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
});

// Licznik dla dynamicznych formularzy
let rangeIndex = <?= count($ranges ?? []) ?>;
let prizeIndex = <?= count($prizes ?? []) ?>;

function addRangeForm() {
    // Template dla nowego zakresu
    const template = `
        <div class="range-form" data-index="${rangeIndex}">
            <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removeRangeForm(this)" title="Usuń zakres">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nazwa zakresu <span class="text-danger">*</span></label>
                        <select class="form-control" name="ranges[${rangeIndex}][range_name]" required>
                            <option value="main">Główny (main)</option>
                            <option value="bonus">Bonus</option>
                            <option value="extra">Dodatkowy (extra)</option>
                        </select>
                        <small class="form-text text-muted">Typ zakresu liczb</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Od <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="ranges[${rangeIndex}][min_number]" value="1" min="1" max="100" required>
                        <small class="form-text text-muted">Min. liczba</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Do <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="ranges[${rangeIndex}][max_number]" value="49" min="2" max="100" required>
                        <small class="form-text text-muted">Maks. liczba</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Ile typować <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="ranges[${rangeIndex}][numbers_to_pick]" value="6" min="1" max="20" required>
                        <small class="form-text text-muted">Liczb do wytypowania</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Kolejność</label>
                        <input type="number" class="form-control" name="ranges[${rangeIndex}][sort_order]" value="${rangeIndex + 1}" min="1">
                        <small class="form-text text-muted">Kolejność wyświetlania</small>
                    </div>
                </div>
                
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="ranges[${rangeIndex}][is_required]" id="range_required_${rangeIndex}" value="1" checked>
                            <label class="form-check-label" for="range_required_${rangeIndex}">Wymagany</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#ranges-container').append(template);
    rangeIndex++;
}

function removeRangeForm(button) {
    $(button).closest('.range-form').remove();
}

function addPrizeForm() {
    // Template dla nowego poziomu wygranej
    const template = `
        <div class="prize-form" data-index="${prizeIndex}">
            <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removePrizeForm(this)" title="Usuń poziom wygranej">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Zakres</label>
                        <select class="form-control" name="prizes[${prizeIndex}][range_name]">
                            <option value="main">Główny</option>
                            <option value="bonus">Bonus</option>
                            <option value="both">Główny + Bonus</option>
                        </select>
                        <small class="form-text text-muted">Którego zakresu dotyczy</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Trafień głównych <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="prizes[${prizeIndex}][numbers_matched]" value="3" min="0" max="20" required>
                        <small class="form-text text-muted">Ile liczb głównych</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Trafień bonus</label>
                        <input type="number" class="form-control" name="prizes[${prizeIndex}][bonus_matched]" value="0" min="0" max="10">
                        <small class="form-text text-muted">Ile liczb bonus</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Kwota (zł)</label>
                        <input type="number" class="form-control" name="prizes[${prizeIndex}][prize_amount]" step="0.01" min="0">
                        <small class="form-text text-muted">Stała kwota wygranej</small>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Procent (%)</label>
                        <input type="number" class="form-control" name="prizes[${prizeIndex}][prize_percentage]" step="0.01" min="0" max="100">
                        <small class="form-text text-muted">% puli nagród</small>
                    </div>
                </div>
                
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="prizes[${prizeIndex}][is_jackpot]" id="prize_jackpot_${prizeIndex}" value="1">
                            <label class="form-check-label" for="prize_jackpot_${prizeIndex}">Jackpot</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Opis wygranej</label>
                        <input type="text" class="form-control" name="prizes[${prizeIndex}][prize_description]" placeholder="np. Trafienie 3 liczb">
                        <small class="form-text text-muted">Opcjonalny opis wygranej</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#prizes-container').append(template);
    prizeIndex++;
}

function removePrizeForm(button) {
    $(button).closest('.prize-form').remove();
}
</script>
<?= $this->endSection() ?>