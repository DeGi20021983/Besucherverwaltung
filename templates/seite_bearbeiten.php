<?php
/**
 * @var array $formular @var array $fehler @var string $eigeneUrl
 * @var bool $gespeichert @var string $absageFehler
 */
?>
<h1>Anmeldung ändern oder absagen</h1>

<?php if ($gespeichert): ?>
<div class="meldung meldung-erfolg" role="status">Die Änderungen wurden gespeichert. Der Bearbeitungslink bleibt gültig.</div>
<?php endif; ?>

<p class="einleitung">Sie können alle Angaben ändern, Personen hinzufügen oder entfernen oder die Anmeldung komplett absagen.</p>

<?php require APP_ROOT . '/templates/fehler_zusammenfassung.php'; ?>

<?php
$aktionUrl = $eigeneUrl;
$absendenText = 'Änderungen speichern';
require APP_ROOT . '/templates/formular.php';
?>

<form method="post" action="<?= e($eigeneUrl) ?>" class="karte absage" id="absage">
    <?= csrfFeld() ?>
    <?= honeypotFeld() ?>
    <h2>Anmeldung absagen</h2>
    <p>Bei einer Absage wird die Anmeldung mit allen Personen sofort und endgültig gelöscht.</p>
    <div class="feld feld-checkbox<?= $absageFehler ? ' hat-fehler' : '' ?>">
        <label>
            <input type="checkbox" name="absage_bestaetigt" value="1" required<?= $absageFehler ? ' aria-invalid="true"' : '' ?> aria-describedby="fehler-absage">
            Ja, ich möchte diese Anmeldung absagen.
        </label>
        <p class="feld-fehler" id="fehler-absage"<?= $absageFehler ? '' : ' hidden' ?>><?= e($absageFehler) ?></p>
    </div>
    <button type="submit" name="aktion" value="absagen" class="button button-gefahr">Anmeldung absagen</button>
</form>
