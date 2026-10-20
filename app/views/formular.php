<?php
/**
 * Gemeinsame Formularfelder für Anmelden und Ändern.
 *
 * @var array  $v       Formularwerte
 * @var array  $f       Fehler (Feld => Meldung, "personen" => [Index => Meldung])
 * @var string $heute   heutiges Datum (Y-m-d) für das min-Attribut
 */
$pf = $f['personen'] ?? [];
$anzahl = count($v['personen']);
?>
<section class="karte">
<h2 class="karte-titel">Besuch</h2>
<div class="stapel">

<div class="feld">
<label class="lbl" for="einrichtung">Name der Einrichtung</label>
<input class="inp" id="einrichtung" name="einrichtung" type="text" maxlength="200" autocomplete="organization" required
 value="<?= e($v['einrichtung']) ?>"<?= feld_attr($f['einrichtung'] ?? null, 'e-einrichtung') ?>>
<?= feld_fehler($f['einrichtung'] ?? null, 'e-einrichtung') ?>
</div>

<fieldset class="feld feld-gruppe" id="art"<?= isset($f['art']) ? ' aria-invalid="true" aria-describedby="e-art"' : '' ?>>
<legend class="lbl">Art der Einrichtung</legend>
<div class="segmente">
<?php foreach (EINRICHTUNG_ARTEN as $art): ?>
<label class="seg"><input type="radio" name="art" value="<?= e($art) ?>" required<?= $v['art'] === $art ? ' checked' : '' ?>><span><?= e($art) ?></span></label>
<?php endforeach; ?>
</div>
<?= feld_fehler($f['art'] ?? null, 'e-art') ?>
</fieldset>

<div class="raster2">
<div class="feld">
<label class="lbl" for="datum">Tag</label>
<input class="inp" id="datum" name="datum" type="date" min="<?= e($heute) ?>" required
 value="<?= e($v['datum']) ?>"<?= feld_attr($f['datum'] ?? null, 'e-datum') ?>>
<?= feld_fehler($f['datum'] ?? null, 'e-datum') ?>
</div>
<div class="feld">
<label class="lbl" for="zeit">Uhrzeit (HH:MM)</label>
<input class="inp" id="zeit" name="zeit" type="time" step="60" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="09:30" required
 value="<?= e($v['zeit']) ?>"<?= feld_attr($f['zeit'] ?? null, 'e-zeit') ?>>
<?= feld_fehler($f['zeit'] ?? null, 'e-zeit') ?>
</div>
</div>

<div class="feld">
<label class="lbl" for="betreff">Betreff</label>
<input class="inp" id="betreff" name="betreff" type="text" maxlength="255" required
 value="<?= e($v['betreff']) ?>"<?= feld_attr($f['betreff'] ?? null, 'e-betreff') ?>>
<?= feld_fehler($f['betreff'] ?? null, 'e-betreff') ?>
</div>

</div>
</section>

<section class="karte">
<h2 class="karte-titel mit-text">Ansprechperson</h2>
<p class="karte-text">Die Person im Haus, die den Besuch empfängt.</p>
<div class="raster2">
<div class="feld">
<label class="lbl" for="vorname">Vorname</label>
<input class="inp" id="vorname" name="vorname" type="text" maxlength="100" autocomplete="given-name" required
 value="<?= e($v['vorname']) ?>"<?= feld_attr($f['vorname'] ?? null, 'e-vorname') ?>>
<?= feld_fehler($f['vorname'] ?? null, 'e-vorname') ?>
</div>
<div class="feld">
<label class="lbl" for="nachname">Nachname</label>
<input class="inp" id="nachname" name="nachname" type="text" maxlength="100" autocomplete="family-name" required
 value="<?= e($v['nachname']) ?>"<?= feld_attr($f['nachname'] ?? null, 'e-nachname') ?>>
<?= feld_fehler($f['nachname'] ?? null, 'e-nachname') ?>
</div>
</div>
</section>

<section class="karte">
<h2 class="karte-titel mit-text">Externe Personen</h2>
<p class="karte-text">Einrichtung, Termin, Betreff und Ansprechperson gelten für alle Personen dieser Anmeldung.</p>
<div class="stapel">
<div class="stapel" id="personen" data-personen>
<?php foreach ($v['personen'] as $i => $name): ?>
<?= render('person', ['i' => $i, 'name' => $name, 'fehler' => $pf[$i] ?? null, 'anzahl' => $anzahl]) ?>
<?php endforeach; ?>
</div>
<div>
<button type="submit" class="btn2" name="aktion" value="person_hinzufuegen" formnovalidate data-person-hinzufuegen>
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Weitere Person hinzufügen</button>
</div>
</div>
</section>

<template id="person-vorlage">
<?= render('person', ['i' => 0, 'name' => '', 'fehler' => null, 'anzahl' => 2]) ?>
</template>
