<?php /** @var array $fehler */ ?>
<?php if (!empty($fehler['_allgemein'])): ?>
<div class="meldung meldung-fehler" role="alert"><?= e($fehler['_allgemein']) ?></div>
<?php elseif ($fehler): ?>
<div class="meldung meldung-fehler" role="alert" id="fehler-zusammenfassung">
    Bitte prüfen Sie Ihre Angaben: <?= count($fehler) === 1 ? 'Ein Feld ist' : count($fehler) . ' Felder sind' ?> nicht korrekt ausgefüllt.
</div>
<?php else: ?>
<div class="meldung meldung-fehler" role="alert" id="fehler-zusammenfassung" hidden></div>
<?php endif; ?>
