<?php
/** @var array $v @var array $f @var string|null $hinweis @var string $heute @var string $selbst @var bool $gespeichert */
?>
<h1 class="titel">Anmeldung ändern</h1>
<p class="lead">Änderungen gelten sofort für den Pfortendienst. Es gelten dieselben Regeln wie bei der Anmeldung.</p>

<form method="post" action="<?= e($selbst) ?>" novalidate class="formular" data-formular>
<?= csrf_field() ?>
<?= render('standard_button') ?>
<?= honeypot_field() ?>
<?= render('fehler_hinweis', ['f' => $f, 'hinweis' => $hinweis, 'titel' => 'Die Änderungen wurden noch nicht gespeichert.']) ?>
<?php if ($gespeichert): ?>
<div class="alert alert-ok" role="status">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg>
<div class="alert-titel">Änderungen gespeichert.</div>
</div>
<?php endif; ?>
<?= render('formular', ['v' => $v, 'f' => $f, 'heute' => $heute]) ?>

<div class="absenden">
<button type="submit" class="btn" name="aktion" value="speichern">Änderungen speichern</button>
</div>
</form>

<section class="karte absagen" aria-labelledby="absagen-titel">
<h2 class="karte-titel mit-text" id="absagen-titel">Anmeldung absagen</h2>
<p class="karte-text">Der Besuch wird komplett abgesagt. Die Anmeldung mit allen Personen wird dabei gelöscht.</p>
<button type="button" class="btnd" data-absage-frage hidden>Anmeldung absagen</button>
<form method="post" action="<?= e($selbst) ?>" class="absage-box" role="alertdialog" aria-labelledby="absagen-frage" data-absage-box>
<?= csrf_field() ?>
<?= honeypot_field('website-absage') ?>
<p id="absagen-frage" class="absage-frage">Möchten Sie die Anmeldung wirklich absagen? Sie wird endgültig gelöscht und kann nicht wiederhergestellt werden.</p>
<div class="aktionen-klein">
<button type="submit" class="btnr" name="aktion" value="absagen">Ja, endgültig absagen</button>
<button type="button" class="btn2" data-absage-zurueck hidden>Zurück</button>
</div>
</form>
</section>
