<?php
/**
 * Hinweis oberhalb des Formulars. Wird auch von app.js für die
 * clientseitige Prüfung verwendet (data-fehler-hinweis).
 *
 * @var array $f @var string|null $hinweis @var string $titel
 */
$sichtbar = $f !== [] || $hinweis !== null;
?>
<div class="alert alert-fehler" role="alert" data-fehler-hinweis<?= $sichtbar ? '' : ' hidden' ?>>
<?= icon_fehler(22) ?>
<div>
<?php if ($hinweis !== null): ?>
<div class="alert-titel"><?= e($hinweis) ?></div>
<?php else: ?>
<div class="alert-titel"><?= e($titel) ?></div>
<div class="alert-text">Bitte korrigieren Sie die markierten Felder. Ihre bisherigen Eingaben bleiben erhalten.</div>
<?php endif; ?>
</div>
</div>
