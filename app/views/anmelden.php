<?php /** @var array $v @var array $f @var string|null $hinweis @var string $heute */ ?>
<h1 class="titel">Besuch anmelden</h1>
<p class="lead">Kündigen Sie externe Besucherinnen und Besucher beim Pfortendienst an. Alle Felder sind Pflichtfelder.</p>

<form method="post" action="index.php" novalidate class="formular" data-formular>
<?= csrf_field() ?>
<?= render('standard_button') ?>
<?= honeypot_field() ?>
<?= render('fehler_hinweis', ['f' => $f, 'hinweis' => $hinweis, 'titel' => 'Die Anmeldung wurde noch nicht gespeichert.']) ?>
<?= render('formular', ['v' => $v, 'f' => $f, 'heute' => $heute]) ?>

<section class="datenschutz" aria-labelledby="ds-titel">
<h2 id="ds-titel">Datenschutzhinweis</h2>
<?= datenschutzhinweis() ?>
</section>

<div class="absenden">
<button type="submit" class="btn" name="aktion" value="speichern">Anmeldung absenden</button>
<p class="text-klein">Danach erhalten Sie einen persönlichen Link, mit dem Sie die Anmeldung ändern oder absagen können.</p>
</div>
</form>
