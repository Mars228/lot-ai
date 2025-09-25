<?php

// ==========================================
// app/Views/layouts/main.php
// ==========================================
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Analizator Gier Liczbowych') ?></title>
    
    <!-- Bootstrap CSS 5.3.7 -->
    <link href="<?= base_url('/assets/vendor/bootstrap/5.3.7/css/bootstrap.min.css') ?>" rel="stylesheet">
    
    <!-- AdminLTE CSS 4.0.0-rc4 -->
    <link href="<?= base_url('/assets/vendor/admin-lte/4.0.0-rc4/css/adminlte.min.css') ?>" rel="stylesheet">
    
    <!-- Font Awesome 6.4.0 -->
    <link href="<?= base_url('/assets/vendor/fontawesome/6.4.0/css/all.min.css') ?>" rel="stylesheet">
    
    <!-- Toastr CSS 2.1.4 -->
    <link href="<?= base_url('/assets/vendor/toastr/2.1.4/toastr.min.css') ?>" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= base_url('/assets/css/site.css') ?>" rel="stylesheet">
    
    <!-- Dodatkowe style dla konkretnych stron -->
    <?= $this->renderSection('styles') ?>
</head>
<body class="bg-light">
    
    <!-- Navigation -->
    <?= view('partials/page_nav', $viewData ?? []) ?>

    <!-- Main Content -->
    <main class="container-fluid py-4">
        
        <!-- Page Header -->
        <?php if (isset($pageTitle)): ?>
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h2 mb-1"><?= esc($pageTitle) ?></h1>
                <?php if (isset($pageSubtitle)): ?>
                    <p class="text-muted mb-0"><?= esc($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $breadcrumb): ?>
                            <?php if (isset($breadcrumb['url'])): ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= esc($breadcrumb['url']) ?>"><?= esc($breadcrumb['title']) ?></a>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?= esc($breadcrumb['title']) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?= $this->renderSection('content') ?>
        
    </main>

    <!-- Footer -->
    <?= view('partials/page_footer', $viewData ?? []) ?>

    <!-- jQuery 3.7.x -->
    <script src="<?= base_url('/assets/vendor/jquery/jquery-3.7.1.min.js') ?>"></script>
    
    <!-- Bootstrap Bundle 5.3.7 -->
    <script src="<?= base_url('/assets/vendor/bootstrap/5.3.7/js/bootstrap.bundle.min.js') ?>"></script>
    
    <!-- AdminLTE App 4.0.0-rc4 -->
    <script src="<?= base_url('/assets/vendor/admin-lte/4.0.0-rc4/js/adminlte.min.js') ?>"></script>
    
    <!-- Input Mask -->
    <script src="<?= base_url('/assets/vendor/inputmask/jquery.inputmask.min.js') ?>"></script>
    
    <!-- Toastr 2.1.4 -->
    <script src="<?= base_url('/assets/vendor/toastr/2.1.4/toastr.min.js') ?>"></script>
    
    <!-- Custom JS -->
    <script src="<?= base_url('/assets/js/site.js') ?>"></script>

    <!-- Wyświetlanie komunikatów -->
    <?php if (session()->getFlashdata('message')): ?>
    <script>
        $(document).ready(function() {
            var messageType = '<?= session()->getFlashdata('messageType') ?? 'info' ?>';
            var message = '<?= addslashes(session()->getFlashdata('message')) ?>';
            
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            };
            
            switch(messageType) {
                case 'success':
                    toastr.success(message);
                    break;
                case 'error':
                    toastr.error(message);
                    break;
                case 'warning':
                    toastr.warning(message);
                    break;
                default:
                    toastr.info(message);
            }
        });
    </script>
    <?php endif; ?>

    <!-- Auto-check sync status -->
    <script>
    $(document).ready(function() {
        checkSyncStatus();
        // Sprawdzaj co 30 sekund
        setInterval(checkSyncStatus, 30000);
    });

    function checkSyncStatus() {
        $.get('/draws/sync-status', function(response) {
            if (response.success) {
                const statusEl = document.getElementById('syncStatus');
                if (response.in_progress) {
                    statusEl.innerHTML = '<i class="fas fa-sync-alt fa-spin text-primary"></i> <small>Synchronizacja...</small>';
                } else {
                    const lastSync = response.formatted_date;
                    statusEl.innerHTML = `<i class="fas fa-check text-success"></i> <small>Sync: ${lastSync}</small>`;
                }
            }
        }).fail(function() {
            document.getElementById('syncStatus').innerHTML = '<i class="fas fa-times text-danger"></i> <small>Błąd sync</small>';
        });
    }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>