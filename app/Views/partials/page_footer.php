<?php
// ==========================================
// app/Views/partials/page_footer.php - Stopka strony
// ==========================================
?>

<!-- Main Footer -->
<footer class="main-footer">
    <div class="row">
        <div class="col-md-6">
            <strong>
                <i class="fas fa-dice mr-2"></i>
                Analizator Gier Liczbowych &copy; <?= date('Y') ?>
            </strong>
            <br>
            <small class="text-muted">
                System analizy i prognozowania wyników gier liczbowych
            </small>
        </div>
        
        <div class="col-md-6 text-right">
            <div class="mb-2">
                <strong>Wersja:</strong> <?= esc($appVersion ?? '1.0') ?>
                <br>
                <strong>PHP:</strong> <?= $systemInfo['php_version'] ?? PHP_VERSION ?>
                <br>
                <strong>CodeIgniter:</strong> <?= $systemInfo['ci_version'] ?? \CodeIgniter\CodeIgniter::CI_VERSION ?>
            </div>
            
            <!-- Status połączeń -->
            <div class="footer-status">
                <small>
                    <span class="badge badge-<?= $dbStatus['badge_class'] ?? 'secondary' ?> mr-1">
                        <i class="fas fa-database mr-1"></i>
                        DB: <?= $dbStatus['status'] === 'online' ? 'Online' : 'Offline' ?>
                    </span>
                    
                    <span class="badge badge-<?= $apiStatus['badge_class'] ?? 'secondary' ?> mr-1">
                        <i class="fas fa-plug mr-1"></i>
                        API: <?= $apiStatus['message'] ?? 'N/A' ?>
                    </span>
                    
                    <span class="badge badge-secondary">
                        <i class="fas fa-clock mr-1"></i>
                        <?= $systemInfo['current_time'] ?? date('H:i:s') ?>
                    </span>
                </small>
            </div>
        </div>
    </div>
    
    <!-- Dodatkowe informacje (rozwijane) -->
    <div class="row mt-3" style="display: none;" id="footerDetails">
        <div class="col-12">
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <h6><i class="fas fa-server mr-2"></i>Informacje serwera</h6>
                    <small>
                        <strong>OS:</strong> <?= $systemInfo['os'] ?? 'N/A' ?><br>
                        <strong>Memory:</strong> <?= $systemInfo['memory_usage'] ?? 'N/A' ?> / <?= $systemInfo['memory_limit'] ?? 'N/A' ?><br>
                        <strong>Timezone:</strong> <?= $systemInfo['timezone'] ?? 'N/A' ?>
                    </small>
                </div>
                
                <div class="col-md-4">
                    <h6><i class="fas fa-database mr-2"></i>Baza danych</h6>
                    <small>
                        <?php if (($dbStatus['status'] ?? 'offline') === 'online'): ?>
                            <strong>MySQL:</strong> <?= $dbStatus['version'] ?? 'N/A' ?><br>
                            <strong>Tabele:</strong> <?= $dbStatus['tables'] ?? 0 ?><br>
                            <strong>Rozmiar:</strong> <?= $systemInfo['database_size'] ?? 'N/A' ?>
                        <?php else: ?>
                            <span class="text-danger">Brak połączenia z bazą danych</span>
                        <?php endif; ?>
                    </small>
                </div>
                
                <div class="col-md-4">
                    <h6><i class="fas fa-chart-bar mr-2"></i>Statystyki</h6>
                    <small>
                        <strong>Gry:</strong> <?= count($games ?? []) ?><br>
                        <strong>Losowania:</strong> <?= number_format($systemInfo['total_draws'] ?? 0) ?><br>
                        <strong>Ostatnia sync:</strong> <?= isset($systemInfo['last_update']) ? format_datetime_polish($systemInfo['last_update']) : 'Nigdy' ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toggle dla dodatkowych szczegółów -->
    <div class="text-center mt-2">
        <button type="button" class="btn btn-link btn-sm text-muted" onclick="toggleFooterDetails()">
            <i class="fas fa-chevron-down" id="footerToggleIcon"></i>
            <span id="footerToggleText">Pokaż szczegóły</span>
        </button>
    </div>
</footer>

<script>
function toggleFooterDetails() {
    const details = document.getElementById('footerDetails');
    const icon = document.getElementById('footerToggleIcon');
    const text = document.getElementById('footerToggleText');
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
        text.textContent = 'Ukryj szczegóły';
    } else {
        details.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
        text.textContent = 'Pokaż szczegóły';
    }
}
</script>