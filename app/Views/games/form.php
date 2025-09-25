<?php
// ==========================================
// app/Views/games/form.php
// ==========================================
?>

<!-- ======================================== -->
<!-- 4. VIEW: app/Views/games/form.php -->
<!-- Formularz dodawania/edycji gry -->
<!-- ======================================== -->

<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <a href="/games" class="btn btn-link p-0">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <?= $game ? 'Edytuj grę' : 'Dodaj grę' ?>
                    </h3>
                </div>
                
                <form action="/games/save" method="post">
                    <?= csrf_field() ?>
                    <?php if ($game): ?>
                        <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
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

                        <div class="form-group">
                            <label for="name">Nazwa gry <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control" 
                                   value="<?= old('name', $game['name'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug (URL) <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="slug" 
                                   id="slug" 
                                   class="form-control" 
                                   value="<?= old('slug', $game['slug'] ?? '') ?>"
                                   pattern="[a-z0-9-]+"
                                   required>
                            <small class="form-text text-muted">Tylko małe litery, cyfry i myślniki</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Opis gry</label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control" 
                                      rows="3"><?= old('description', $game['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bet_price">Cena zakładu (zł) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="bet_price" 
                                           id="bet_price" 
                                           class="form-control" 
                                           value="<?= old('bet_price', $game['bet_price'] ?? '') ?>"
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_game_type">Typ gry w API</label>
                                    <input type="text" 
                                           name="api_game_type" 
                                           id="api_game_type" 
                                           class="form-control" 
                                           value="<?= old('api_game_type', $game['api_game_type'] ?? '') ?>">
                                    <small class="form-text text-muted">np. Lotto, MultiMulti, EuroJackpot</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="logo_filename">Nazwa pliku logo</label>
                            <input type="text" 
                                   name="logo_filename" 
                                   id="logo_filename" 
                                   class="form-control" 
                                   value="<?= old('logo_filename', $game['logo_filename'] ?? '') ?>">
                            <small class="form-text text-muted">Plik powinien znajdować się w /public/assets/img/</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       <?= (!$game || $game['is_active']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_active">
                                    Gra aktywna
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Zapisz
                        </button>
                        <a href="/games" class="btn btn-default">
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
                        <i class="fas fa-info-circle"></i> Informacje
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Wskazówki:</h6>
                    <ul class="small">
                        <li>Nazwa gry będzie wyświetlana w aplikacji</li>
                        <li>Slug używany jest w adresach URL</li>
                        <li>Cena zakładu to podstawowa stawka za grę</li>
                        <li>Logo gry powinno mieć format PNG lub JPG</li>
                    </ul>
                    
                    <h6>Przykładowe gry:</h6>
                    <ul class="small">
                        <li><strong>Lotto</strong> - slug: lotto</li>
                        <li><strong>Multi Multi</strong> - slug: multi-multi</li>
                        <li><strong>Eurojackpot</strong> - slug: eurojackpot</li>
                        <li><strong>Mini Lotto</strong> - slug: mini-lotto</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-generowanie slug z nazwy
    $('#name').on('blur', function() {
        if (!$('#slug').val()) {
            var slug = $(this).val()
                .toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
            $('#slug').val(slug);
        }
    });
});
</script>
<?= $this->endSection() ?>