<?php
/** @var array $anmeldung */
$termin = new DateTimeImmutable($anmeldung['termin']);
?>
<section class="karte">
    <h2>Ihre Angaben</h2>
    <dl class="zusammenfassung">
        <dt>Einrichtung</dt>
        <dd><?= e($anmeldung['einrichtung_name']) ?> (<?= e(einrichtungArtLabel($anmeldung['einrichtung_art'])) ?>)</dd>
        <dt>Termin</dt>
        <dd><?= e(datumLang($termin)) ?>, <?= e($termin->format('H:i')) ?> Uhr</dd>
        <dt>Betreff</dt>
        <dd><?= e($anmeldung['betreff']) ?></dd>
        <dt>Ansprechperson</dt>
        <dd><?= e($anmeldung['ansprech_vorname'] . ' ' . $anmeldung['ansprech_nachname']) ?></dd>
        <dt>Externe Person<?= count($anmeldung['personen']) > 1 ? 'en' : '' ?></dt>
        <dd>
            <ul class="liste-schlicht">
                <?php foreach ($anmeldung['personen'] as $name): ?>
                <li><?= e($name) ?></li>
                <?php endforeach; ?>
            </ul>
        </dd>
    </dl>
</section>
