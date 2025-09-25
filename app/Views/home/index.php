<?php
// ==========================================
// app/Views/home/index.php
// ==========================================
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Podsumowanie ostatnich losowań -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-ol mr-2"></i>
                    Ostatnie losowania
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($latestDraws)): ?>
                    <div class="row">
                        <?php foreach ($latestDraws as $item): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['game']['logo_filename']): ?>
                                            <img src="<?= base_url('assets/img/' . $item['game']['logo_filename']) ?>" 
                                                 alt="<?= esc($item['game']['name']) ?>" 
                                                 class="img-circle mr-3" 
                                                 style="width: 50px; height: 50px;">
                                        <?php else: ?>
                                            <i class="<?= get_game_icon($item['game']['slug']) ?> fa-2x mr-3 text-primary"></i>
                                        <?php endif; ?>
                                        <div>
                                            <h5 class="mb-1"><?= esc($item['game']['name']) ?></h5>
                                            <p class="mb-1">
                                                <strong>Nr <?= $item['draw']['draw_number'] ?></strong> 
                                                z <?= format_date_polish($item['draw']['draw_date']) ?>
                                            </p>
                                            <div class="lottery-numbers">
                                                <?php 
                                                $mainNumbers = json_decode($item['draw']['numbers_main'], true);
                                                if ($mainNumbers): 
                                                ?>
                                                    <span class="badge badge-primary mr-1">
                                                        <?= format_lottery_numbers($mainNumbers) ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                $bonusNumbers = json_decode($item['draw']['numbers_bonus'], true);
                                                if ($bonusNumbers): 
                                                ?>
                                                    <span class="badge badge-secondary">
                                                        <?= format_lottery_numbers($bonusNumbers) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Brak dostępnych losowań. Dodaj gry i zaimportuj dane losowań.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Statystyki wygranych -->
<div class="row">
    <!-- Tygodniowe wygrane -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy mr-2"></i>
                    Wygrane w tym tygodniu
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($weeklyWins)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Gra</th>
                                    <th>Kwota</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($weeklyWins, 0, 5) as $win): ?>
                                <tr>
                                    <td><?= esc($win['game_name']) ?></td>
                                    <td><?= format_currency_polish($win['prize_amount']) ?></td>
                                    <td><?= format_date_polish($win['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($weeklyWins) > 5): ?>
                        <div class="text-center mt-2">
                            <a href="<?= base_url('/results') ?>" class="btn btn-sm btn-outline-primary">
                                Zobacz wszystkie (<?= count($weeklyWins) ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p>Brak wygranych w tym tygodniu</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Miesięczne wygrane -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar mr-2"></i>
                    Wygrane w tym miesiącu
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($monthlyWins)): ?>
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-coins"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Łączna kwota</span>
                                    <span class="info-box-number">
                                        <?= format_currency_polish(array_sum(array_column($monthlyWins, 'prize_amount'))) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-ticket-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Liczba wygranych</span>
                                    <span class="info-box-number"><?= count($monthlyWins) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="<?= base_url('/results') ?>" class="btn btn-primary">
                            <i class="fas fa-eye mr-2"></i>
                            Szczegóły wygranych
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>Brak wygranych w tym miesiącu</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacje systemowe -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informacje systemowe
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-database"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Rozmiar bazy</span>
                                <span class="info-box-number"><?= esc($systemInfo['database_size']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-list-ol"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Losowania</span>
                                <span class="info-box-number"><?= number_format($systemInfo['total_draws']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-ticket-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Zakłady</span>
                                <span class="info-box-number"><?= number_format($systemInfo['total_bets']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger">
                                <i class="fas fa-trophy"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Wygrane</span>
                                <span class="info-box-number"><?= number_format($systemInfo['winning_bets']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        Ostatnia aktualizacja: 
                        <?= $systemInfo['last_update'] ? format_datetime_polish($systemInfo['last_update']) : 'Nigdy' ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>