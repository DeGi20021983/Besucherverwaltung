<?php /** @var array $formular @var array $fehler */ ?>
<h1>Besuch anmelden</h1>
<p class="einleitung">Kündigen Sie hier externe Besucherinnen und Besucher beim Pfortendienst an. Alle Felder sind Pflichtfelder.</p>

<?php require APP_ROOT . '/templates/fehler_zusammenfassung.php'; ?>

<?php
$aktionUrl = 'index.php';
$absendenText = 'Besuch anmelden';
require APP_ROOT . '/templates/formular.php';
