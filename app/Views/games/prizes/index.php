<!-- ======================================== -->
<!-- 2. VIEW: app/Views/games/prizes/index.php -->
<!-- Zarządzanie wygranymi -->
<!-- ======================================== -->

<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <a href="/games" class="btn btn-link p-0">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        Wygrane - <?= esc($game['name']) ?>
                    </h3>
                    <div>
                        <a href="/games/<?= $game['id'] ?>/prizes/add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Dodaj wygraną
                        </a>
                        <?php if ($game['slug'] === 'multi-multi'): ?>
                        <a href="/games/<?= $game['id'] ?>/prizes/import-multi-multi" 
                           class="btn btn-success btn-sm"
                           onclick="return confirm('Czy chcesz zaimportować domyślne wygrane Multi Multi?')">
                            <i class="fas fa-file-import"></i> Import Multi Multi
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Zakładki dla wariantów -->
                    <?php if (!empty($variants)): ?>
                    <ul class="nav nav-tabs" id="variants-tab" role="tablist">
                        <?php foreach ($variants as $index => $variant): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                               id="variant-<?= $variant['numbers_to_select'] ?>-tab" 
                               data-toggle="tab" 
                               href="#variant-<?= $variant['numbers_to_select'] ?>" 
                               role="tab">
                                <?= esc($variant['variant_name']) ?>
                                <span class="badge badge-info ml-2"><?= $variant['prize_levels'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <!-- Zawartość zakładek -->
                    <div class="tab-content mt-3" id="variants-tabContent">
                        <?php if (!empty($prizes)): ?>
                            <?php foreach ($prizes as $numbersSelected => $variantPrizes): ?>
                            <div class="tab-pane fade <?= $numbersSelected == ($variants[0]['numbers_to_select'] ?? 1) ? 'show active' : '' ?>" 
                                 id="variant-<?= $numbersSelected ?>" 
                                 role="tabpanel">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="150">Trafione liczby</th>
                                                <th width="150">Kwota wygranej</th>
                                                <th>Opis</th>
                                                <th width="100">Jackpot</th>
                                                <th width="120">Akcje</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($variantPrizes as $prize): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">
                                                        <?= $prize['numbers_matched'] ?> z <?= $numbersSelected ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <strong><?= number_format($prize['prize_amount'], 2) ?> zł</strong>
                                                </td>
                                                <td><?= esc($prize['prize_description']) ?></td>
                                                <td class="text-center">
                                                    <?php if ($prize['is_jackpot']): ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-crown"></i> TAK
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">NIE</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="/games/<?= $game['id'] ?>/prizes/<?= $prize['id'] ?>/edit" 
                                                           class="btn btn-info">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="/games/<?= $game['id'] ?>/prizes/<?= $prize['id'] ?>/delete" 
                                                           class="btn btn-danger"
                                                           onclick="return confirm('Czy na pewno usunąć tę wygraną?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Nie dodano jeszcze żadnych wygranych dla tej gry.
                                <?php if ($game['slug'] === 'multi-multi'): ?>
                                    <br>Możesz użyć przycisku "Import Multi Multi" aby automatycznie dodać wszystkie wygrane.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>