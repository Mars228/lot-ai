<?php
// ==========================================
// app/Views/games/index.php
// ==========================================
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Gry liczbowe</h3>
                    <a href="/games/add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Dodaj grę
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($games as $game): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <?php if ($game['logo_filename']): ?>
                                <img src="/assets/img/<?= esc($game['logo_filename']) ?>" 
                                     class="card-img-top p-3" 
                                     alt="<?= esc($game['name']) ?>"
                                     style="max-height: 150px; object-fit: contain;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= esc($game['name']) ?></h5>
                                    <p class="card-text"><?= esc($game['description']) ?></p>
                                    <p class="text-muted">
                                        Cena zakładu: <strong><?= number_format($game['bet_price'], 2) ?> zł</strong>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <a href="/games/<?= esc($game['slug']) ?>" class="btn btn-info">
                                            <i class="fas fa-eye"></i> Podgląd
                                        </a>
                                        <a href="/games/<?= $game['id'] ?>/prizes" class="btn btn-success">
                                            <i class="fas fa-trophy"></i> Wygrane
                                        </a>
                                        <a href="/games/<?= $game['id'] ?>/edit" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edytuj
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
