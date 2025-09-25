<?php
// ==========================================
// app/Views/partials/page_nav.php - Główna nawigacja
// ==========================================
?>

<!-- Main Navigation -->
<nav class="main-header navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Brand/Logo -->
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <img src="<?= base_url('/assets/img/logo.svg') ?>" 
                 alt="Logo" 
                 width="30" 
                 height="30" 
                 class="d-inline-block align-top me-2">
            <strong>Analizator Gier</strong>
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                
                <!-- Strona główna -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentController === 'home') ? 'active' : '' ?>" 
                       href="<?= base_url('/') ?>">
                        <i class="fas fa-home me-1"></i>
                        Strona główna
                    </a>
                </li>

                <!-- Gry -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentController === 'games') ? 'active' : '' ?>" 
                       href="<?= base_url('/games') ?>">
                        <i class="fas fa-dice me-1"></i>
                        Gry
                        <?php if (isset($games) && count($games) > 0): ?>
                            <span class="badge bg-light text-dark ms-1"><?= count($games) ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Losowania - dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= ($currentController === 'draws') ? 'active' : '' ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="fas fa-list-ol me-1"></i>
                        Losowania
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?= base_url('/draws') ?>">
                                <i class="fas fa-list me-2"></i>
                                Wszystkie
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (isset($games) && !empty($games)): ?>
                            <?php foreach ($games as $game): ?>
                            <li>
                                <a class="dropdown-item <?= (isset($currentGame) && $currentGame['id'] === $game['id']) ? 'active' : '' ?>" 
                                   href="<?= base_url('/draws/' . $game['slug']) ?>">
                                    <i class="<?= get_game_icon($game['slug']) ?> me-2"></i>
                                    <?= esc($game['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>
                                <span class="dropdown-item text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Brak gier
                                </span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Statystyki -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentController === 'statistics') ? 'active' : '' ?>" 
                       href="<?= base_url('/statistics') ?>">
                        <i class="fas fa-chart-line me-1"></i>
                        Statystyki
                        <span class="badge bg-warning text-dark ms-1">6 modeli</span>
                    </a>
                </li>

                <!-- Strategie -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentController === 'strategies') ? 'active' : '' ?>" 
                       href="<?= base_url('/strategies') ?>">
                        <i class="fas fa-lightbulb me-1"></i>
                        Strategie
                        <span class="badge bg-success ms-1">3 typy</span>
                    </a>
                </li>

                <!-- Zakłady -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentController === 'bets') ? 'active' : '' ?>" 
                       href="<?= base_url('/bets') ?>">
                        <i class="fas fa-ticket-alt me-1"></i>
                        Zakłady
                    </a>
                </li>

                <!-- Wyniki - dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= ($currentController === 'results') ? 'active' : '' ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="fas fa-trophy me-1"></i>
                        Wyniki
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?= base_url('/results') ?>">
                                <i class="fas fa-list me-2"></i>
                                Wszystkie wygrane
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('/results/weekly') ?>">
                                <i class="fas fa-calendar-week me-2"></i>
                                Tygodniowe
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('/results/monthly') ?>">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Miesięczne
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('/results/yearly') ?>">
                                <i class="fas fa-calendar me-2"></i>
                                Roczne
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- Right side navbar -->
            <ul class="navbar-nav">
                <!-- Sync Status -->
                <li class="nav-item">
                    <span class="navbar-text me-3" id="syncStatus">
                        <i class="fas fa-sync-alt text-light"></i>
                        <small>Sprawdzanie...</small>
                    </span>
                </li>

                <!-- Ustawienia -->
                <li class="nav-item">
                    <a class="nav-link <?= ($currentController === 'settings') ? 'active' : '' ?>" 
                       href="<?= base_url('/settings') ?>">
                        <i class="fas fa-cogs me-1"></i>
                        Ustawienia
                    </a>
                </li>

                <!-- System Info Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="fas fa-info-circle me-1"></i>
                        <small>v<?= esc($appVersion ?? '1.0') ?></small>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">
                                <i class="fas fa-server me-2"></i>
                                Informacje systemu
                            </h6>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="dropdown-item-text">
                                <small>
                                    <strong>PHP:</strong> <?= $systemInfo['php_version'] ?? PHP_VERSION ?><br>
                                    <strong>CodeIgniter:</strong> <?= $systemInfo['ci_version'] ?? \CodeIgniter\CodeIgniter::CI_VERSION ?><br>
                                    <strong>Memory:</strong> <?= $systemInfo['memory_usage'] ?? 'N/A' ?>
                                </small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="dropdown-item-text">
                                <span class="badge bg-<?= $dbStatus['badge_class'] ?? 'secondary' ?> me-1">
                                    <i class="fas fa-database"></i> DB
                                </span>
                                <span class="badge bg-<?= $apiStatus['badge_class'] ?? 'secondary' ?>">
                                    <i class="fas fa-plug"></i> API
                                </span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('/about') ?>">
                                <i class="fas fa-question-circle me-2"></i>
                                O systemie
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>