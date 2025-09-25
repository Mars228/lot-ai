<?php
// ==========================================
// app/Views/games/index.php
// ==========================================
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista gier liczbowych</h3>
                <div class="card-tools">
                    <a href="<?= base_url('/games/add') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>
                        Dodaj grę
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($games)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Logo</th>
                                    <th>Nazwa</th>
                                    <th>Opis</th>
                                    <th>Cena zakładu</th>
                                    <th>Zakresy liczb</th>
                                    <th>Nagrody</th>
                                    <th style="width: 120px;">Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($games as $game): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($game['logo_filename']): ?>
                                            <img src="<?= base_url('assets/img/' . $game['logo_filename']) ?>" 
                                                 alt="<?= esc($game['name']) ?>" 
                                                 class="img-circle" 
                                                 style="width: 50px; height: 50px;">
                                        <?php else: ?>
                                            <i class="<?= get_game_icon($game['slug']) ?> fa-2x text-primary"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($game['name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($game['slug']) ?></small>
                                        <?php if ($game['api_game_type']): ?>
                                            <br>
                                            <span class="badge badge-info">API: <?= esc($game['api_game_type']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= esc($game['description']) ?>
                                    </td>
                                    <td>
                                        <strong><?= format_currency_polish($game['bet_price']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($game['ranges'])): ?>
                                            <?php foreach ($game['ranges'] as $range): ?>
                                                <div class="mb-1">
                                                    <span class="badge badge-primary">
                                                        <?= ucfirst($range['range_name']) ?>: 
                                                        <?= $range['numbers_to_pick'] ?> z <?= $range['min_number'] ?>-<?= $range['max_number'] ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Brak zakresów</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($game['prizes'])): ?>
                                            <small>
                                                <?php foreach (array_slice($game['prizes'], 0, 3) as $prize): ?>
                                                    <?= $prize['numbers_matched'] ?> traf.: 
                                                    <?= $prize['prize_amount'] ? format_currency_polish($prize['prize_amount']) : ($prize['prize_percentage'] . '%') ?>
                                                    <br>
                                                <?php endforeach; ?>
                                                <?php if (count($game['prizes']) > 3): ?>
                                                    <span class="text-muted">i więcej...</span>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Brak nagród</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('/games/edit/' . $game['id']) ?>" 
                                               class="btn btn-warning" 
                                               title="Edytuj">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    onclick="deleteGame(<?= $game['id'] ?>, '<?= esc($game['name']) ?>')"
                                                    title="Usuń">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fas fa-dice fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Brak gier liczbowych</h4>
                        <p class="text-muted">Dodaj pierwszą grę, aby rozpocząć analizę.</p>
                        <a href="<?= base_url('/games/add') ?>" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Dodaj pierwszą grę
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteGame(gameId, gameName) {
    if (confirm('Czy na pewno chcesz usunąć grę "' + gameName + '"?\n\nUWAGA: Zostaną również usunięte wszystkie powiązane dane (losowania, statystyki, zakłady).')) {
        $.ajax({
            url: '<?= base_url('/games/delete/') ?>' + gameId,
            type: 'DELETE',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Wystąpił błąd podczas usuwania gry.');
            }
        });
    }
}
</script>
<?= $this->endSection() ?>