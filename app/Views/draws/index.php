<?php
// ==========================================
// app/Views/draws/index.php
// ==========================================
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Filtry i informacje -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-ol mr-2"></i>
                    Losowania - <?= esc($currentGame['name']) ?>
                </h3>
                <div class="card-tools">
                    <!-- Przycisk Import CSV -->
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload mr-1"></i>
                        Import CSV
                    </button>
                    
                    <!-- Przycisk Uzupełnij -->
                    <button type="button" class="btn btn-warning btn-sm ml-1" onclick="fillMissingModal()">
    <i class="fas fa-fill-drip"></i> Uzupełnij braki
</button>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filtry -->
                <form method="GET" id="filtersForm" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="game_select">Gra:</label>
                            <select class="form-control" id="game_select" onchange="changeGame()">
                                <?php foreach ($games as $game): ?>
                                <option value="<?= esc($game['slug']) ?>" 
                                        <?= $game['id'] === $currentGame['id'] ? 'selected' : '' ?>>
                                    <?= esc($game['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="year">Rok:</label>
                            <select class="form-control" name="year" id="year" onchange="submitFilters()">
                                <?php foreach ($yearOptions as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $filters['year'] == $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="month">Miesiąc:</label>
                            <select class="form-control" name="month" id="month" onchange="submitFilters()">
                                <?php foreach ($monthOptions as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $filters['month'] == $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>
                                    Filtruj
                                </button>
                                <a href="<?= base_url('/draws/' . $currentGame['slug']) ?>" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times mr-1"></i>
                                    Resetuj
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Statystyki -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-list"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Łącznie</span>
                                <span class="info-box-number"><?= number_format($stats['total_draws']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-calendar"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">W miesiącu</span>
                                <span class="info-box-number"><?= $stats['current_month_draws'] ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-arrow-down"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Najstarszy</span>
                                <span class="info-box-number"><?= $stats['oldest_draw'] ?: 'Brak' ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger">
                                <i class="fas fa-arrow-up"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Najnowszy</span>
                                <span class="info-box-number"><?= $stats['latest_draw'] ?: 'Brak' ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-database"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ostatni import</span>
                                <span class="info-box-number">
                                    <small>
                                        <?= $stats['last_import_source'] ?><br>
                                        <?= format_datetime_polish($stats['last_import_date']) ?>
                                    </small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista losowań -->
                <?php if (!empty($draws)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Numer</th>
                                    <th style="width: 120px;">Data</th>
                                    <th>Liczby główne</th>
                                    <?php if ($currentGame['slug'] === 'eurojackpot'): ?>
                                    <th>Liczby bonus</th>
                                    <?php endif; ?>
                                    <th style="width: 120px;">Kumulacja</th>
                                    <th style="width: 80px;">Źródło</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($draws as $draw): ?>
                                <tr>
                                    <td>
                                        <strong><?= $draw['draw_number'] ?></strong>
                                        <?php if ($currentGame['slug'] === 'eurojackpot' && $draw['draw_number_global']): ?>
                                            <br><small class="text-muted">Global: <?= $draw['draw_number_global'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= format_date_polish($draw['draw_date']) ?>
                                        <?php if ($draw['draw_time']): ?>
                                            <br><small class="text-muted"><?= $draw['draw_time'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="lottery-numbers">
                                            <?php if (!empty($draw['numbers_main'])): ?>
                                                <?php foreach ($draw['numbers_main'] as $number): ?>
                                                    <span class="badge badge-primary mr-1"><?= $number ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Brak danych</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php if ($currentGame['slug'] === 'eurojackpot'): ?>
                                    <td>
                                        <div class="lottery-numbers">
                                            <?php if (!empty($draw['numbers_bonus'])): ?>
                                                <?php foreach ($draw['numbers_bonus'] as $number): ?>
                                                    <span class="badge badge-warning mr-1"><?= $number ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Brak</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if ($draw['jackpot_amount']): ?>
                                            <?= format_currency_polish($draw['jackpot_amount']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $sourceClass = match($draw['data_source']) {
                                            'csv' => 'badge-info',
                                            'api' => 'badge-success', 
                                            'manual' => 'badge-warning',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $sourceClass ?>">
                                            <?= strtoupper($draw['data_source']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Brak losowań</h4>
                        <p class="text-muted">
                            Nie znaleziono losowań dla wybranych kryteriów.<br>
                            Spróbuj zmienić filtry lub zaimportować dane.
                        </p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload mr-2"></i>
                            Importuj dane CSV
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload mr-2"></i>
                    Import danych z pliku CSV
                </h5>
            </div>
            <div class="modal-body">
                <form id="importForm">
                    <input type="hidden" name="game_id" value="<?= $currentGame['id'] ?>">
                    
                    <div class="form-group">
                        <label>Struktura pliku CSV:</label>
                        <div class="alert alert-info">
                            <strong>Wymagane kolumny:</strong><br>
                            <code>numer_losowania, data (YYYY-MM-DD), godzina, liczby_a, liczby_b</code><br><br>
                            <strong>Przykład:</strong><br>
                            <code>1, 2023-01-07, 22:00, "1,5,12,23,34,45", "2,8"</code>
                        </div>
                    </div>
                    
                    <!-- Upload zone -->
                    <div class="form-group">
                        <label>Plik CSV:</label>
                        <div class="csv-upload-zone" id="uploadZone">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <h5>Przeciągnij plik CSV tutaj</h5>
                            <p class="text-muted">lub kliknij aby wybrać plik</p>
                            <p><small>Maksymalny rozmiar: 10MB</small></p>
                        </div>
                        <input type="file" id="csvFileInput" name="csv_file" accept=".csv" style="display: none;">
                    </div>
                    
                    <!-- Wyniki importu -->
                    <div id="importResults" style="display: none;"></div>
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Zamknij</button>
                <button type="button" class="btn btn-primary" onclick="submitImport()" id="importBtn">
                    <i class="fas fa-upload mr-2"></i>
                    Importuj
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Modal Uzupełniania Braków -->
<div class="modal fade" id="fillMissingModal" tabindex="-1" aria-labelledby="fillMissingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fillMissingModalLabel">Uzupełnij brakujące losowania</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Sekcja informacyjna -->
                <div id="infoSection">
                    <div class="text-center p-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Ładowanie...</span>
                        </div>
                        <p class="mt-2">Sprawdzanie brakujących losowań...</p>
                    </div>
                </div>
                
                <!-- Sekcja postępu (ukryta domyślnie) -->
                <div id="progressSection" style="display: none;">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 0%">0%</div>
                    </div>
                    <div class="mt-3 text-center">
                        <div id="fillStatus">Przygotowanie...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
                <button type="button" class="btn btn-primary" id="btnStartFilling" style="display: none;">
                    <i class="fas fa-download"></i> Rozpocznij uzupełnianie
                </button>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Zmiana gry
function changeGame() {
    const gameSlug = document.getElementById('game_select').value;
    window.location.href = '/draws/' + gameSlug;
}

// Submitowanie filtrów
function submitFilters() {
    document.getElementById('filtersForm').submit();
}

// Import CSV
let selectedFile = null;

// Drag & Drop
$(document).ready(function() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('csvFileInput');
    
    uploadZone.addEventListener('click', () => fileInput.click());
    
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
});

function handleFileSelect(file) {
    if (!file.name.toLowerCase().endsWith('.csv')) {
        toastr.error('Można wybrać tylko pliki CSV');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        toastr.error('Plik jest za duży (max 10MB)');
        return;
    }
    
    selectedFile = file;
    
    document.getElementById('uploadZone').innerHTML = `
        <i class="fas fa-file-csv fa-3x mb-3 text-success"></i>
        <h5>${file.name}</h5>
        <p class="text-muted">${(file.size / 1024).toFixed(1)} KB</p>
        <p><small class="text-success">Plik gotowy do importu</small></p>
    `;
    
    document.getElementById('importBtn').disabled = false;
}

function submitImport() {
    if (!selectedFile) {
        toastr.error('Wybierz plik CSV');
        return;
    }
    
    const formData = new FormData();
    formData.append('csv_file', selectedFile);
    formData.append('game_id', <?= $currentGame['id'] ?>);
    
    document.getElementById('importBtn').disabled = true;
    document.getElementById('importBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importowanie...';
    
    $.ajax({
        url: '/draws/import',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            document.getElementById('importBtn').disabled = false;
            document.getElementById('importBtn').innerHTML = '<i class="fas fa-upload mr-2"></i>Importuj';
            
            if (response.success) {
                showImportResults(response.data);
                toastr.success(response.message);
                
                // Odśwież stronę po 3 sekundach
                setTimeout(() => location.reload(), 3000);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            document.getElementById('importBtn').disabled = false;
            document.getElementById('importBtn').innerHTML = '<i class="fas fa-upload mr-2"></i>Importuj';
            toastr.error('Wystąpił błąd podczas importu');
        }
    });
}

function showImportResults(data) {
    let html = `
        <div class="import-results">
            <div class="import-summary">
                <div class="row">
                    <div class="col-4">
                        <div class="import-stat success">
                            <div class="import-stat-value">${data.imported}</div>
                            <div class="import-stat-label">Nowe</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="import-stat warning">
                            <div class="import-stat-value">${data.updated}</div>
                            <div class="import-stat-label">Zaktualizowane</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="import-stat danger">
                            <div class="import-stat-value">${data.errors.length}</div>
                            <div class="import-stat-label">Błędy</div>
                        </div>
                    </div>
                </div>
            </div>
    `;
    
    if (data.errors.length > 0) {
        html += '<div class="alert alert-warning mt-3"><h6>Błędy importu:</h6><ul class="mb-0">';
        data.errors.slice(0, 10).forEach(error => {
            html += `<li>${error}</li>`;
        });
        if (data.errors.length > 10) {
            html += `<li><em>i ${data.errors.length - 10} więcej...</em></li>`;
        }
        html += '</ul></div>';
    }
    
    html += '</div>';
    
    document.getElementById('importResults').innerHTML = html;
    document.getElementById('importResults').style.display = 'block';
}

// Otwieranie modala z informacją o brakach
// Zmienne globalne dla modala
let fillModal = null;
let missingDrawNumbers = [];
let currentGameId = null;

// Funkcja otwierająca modal
function fillMissingModal() {
    const gameId = <?= isset($currentGame['id']) ? $currentGame['id'] : 'null' ?>;
    
    if (!gameId) {
        toastr.error('Nie wybrano gry!');
        return;
    }
    
    currentGameId = gameId;
    
    // Inicjalizuj modal Bootstrap jeśli jeszcze nie istnieje
    if (!fillModal) {
        fillModal = new bootstrap.Modal(document.getElementById('fillMissingModal'), {
            backdrop: 'static',
            keyboard: false
        });
    }
    
    // Reset contentu
    $('#infoSection').html(`
        <div class="text-center p-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Sprawdzanie brakujących losowań...</p>
        </div>
    `);
    $('#progressSection').hide();
    $('#btnStartFilling').hide();
    
    // Pokaż modal
    fillModal.show();
    
    // Pobierz dane o brakach
    $.ajax({
        url: '/draws/checkMissingDraws',
        type: 'POST',
        data: { game_id: gameId },
        success: function(response) {
            if (response.success) {
                // Zaktualizuj zawartość modala
                let infoHtml = `
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td width="50%">Ostatnie w bazie:</td>
                                <td><strong>#${response.last_in_db}</strong> 
                                    <small class="text-muted">(${response.last_date_db})</small>
                                </td>
                            </tr>
                            <tr>
                                <td>Ostatnie dostępne w API:</td>
                                <td><strong>#${response.last_in_api}</strong> 
                                    <small class="text-muted">(${response.last_date_api})</small>
                                </td>
                            </tr>
                            <tr>
                                <td>Liczba brakujących:</td>
                                <td>
                                    <strong class="${response.missing_count > 0 ? 'text-warning' : 'text-success'}">
                                        ${response.missing_count} losowań
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                `;
                
                if (response.missing_count > 0) {
                    infoHtml += `
                        <div class="alert alert-warning">
                            <h6>Numery do pobrania:</h6>
                            <small>${response.missing_numbers.slice(0, 10).join(', ')}
                            ${response.missing_numbers.length > 10 ? 
                                '<br>... i ' + (response.missing_numbers.length - 10) + ' więcej' : ''}
                            </small>
                        </div>
                    `;
                    
                    // Zapisz numery do pobrania
                    missingDrawNumbers = response.missing_numbers;
                    
                    // Pokaż przycisk start
                    $('#btnStartFilling').show();
                    
                } else {
                    infoHtml += `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Baza danych jest aktualna!</strong>
                            <br>Nie ma losowań do pobrania.
                        </div>
                    `;
                }
                
                $('#infoSection').html(infoHtml);
                
            } else {
                $('#infoSection').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        ${response.message || 'Błąd sprawdzania braków'}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            $('#infoSection').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Błąd połączenia z serwerem
                </div>
            `);
        }
    });
}

// Rozpoczęcie uzupełniania (wywoływane przez przycisk)
function startFilling() {
    if (!currentGameId || missingDrawNumbers.length === 0) {
        toastr.error('Brak danych do uzupełnienia');
        return;
    }
    
    // Przełącz widok
    $('#infoSection').slideUp();
    $('#progressSection').slideDown();
    $('#btnStartFilling').prop('disabled', true);
    $('.modal-footer .btn-secondary').prop('disabled', true);
    
    let processed = 0;
    let successCount = 0;
    let errorCount = 0;
    const total = missingDrawNumbers.length;
    
    function updateProgress() {
        const percent = Math.round((processed / total) * 100);
        $('.progress-bar')
            .css('width', percent + '%')
            .text(percent + '%');
    }
    
    function processNext() {
        if (processed >= total) {
            // Zakończono
            $('#fillStatus').html(`
                <div class="text-success">
                    <h5><i class="fas fa-check-circle"></i> Zakończono!</h5>
                    <p>Pobrano: ${successCount} | Błędów: ${errorCount}</p>
                    <small>Odświeżanie strony...</small>
                </div>
            `);
            
            setTimeout(() => {
                fillModal.hide();
                location.reload();
            }, 2000);
            return;
        }
        
        const currentNumber = missingDrawNumbers[processed];
        
        $('#fillStatus').html(`
            <i class="fas fa-spinner fa-spin"></i> 
            Pobieram losowanie <strong>#${currentNumber}</strong>
            <br>
            <small class="text-muted">Postęp: ${processed + 1} z ${total}</small>
        `);
        
        $.ajax({
            url: '/draws/fillSingleDraw',
            type: 'POST',
            dataType: 'json',
            data: {
                game_id: currentGameId,
                draw_number: currentNumber
            },
            success: function(response) {
                if (response && response.success) {
                    console.log(`✓ Pobrano losowanie #${currentNumber}`);
                    successCount++;
                } else {
                    console.error(`✗ Błąd dla #${currentNumber}:`, response?.message);
                    errorCount++;
                }
            },
            error: function(xhr) {
                console.error(`✗ Błąd połączenia dla #${currentNumber}`);
                errorCount++;
            },
            complete: function() {
                processed++;
                updateProgress();
                
                // Opóźnienie między zapytaniami
                const delay = total > 20 ? 1500 : 1000;
                setTimeout(processNext, delay);
            }
        });
    }
    
    // Start
    processNext();
}

// Event listener dla przycisku start
$(document).ready(function() {
    $('#btnStartFilling').on('click', startFilling);
});
</script>

<!-- Debug - usuń po naprawieniu 
<script>
console.log('Current Game:', <?= json_encode($currentGame ?? 'NULL') ?>);
console.log('Game ID:', <?= isset($currentGame['id']) ? $currentGame['id'] : 'UNDEFINED' ?>);
</script> -->

<?= $this->endSection() ?>