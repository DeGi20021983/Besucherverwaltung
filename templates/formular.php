<?php
/**
 * Anmeldeformular (neu und bearbeiten).
 *
 * @var array  $formular
 * @var array  $fehler
 * @var string $aktionUrl
 * @var string $absendenText
 */

$feldAttr = static function (string $feld) use ($fehler): string {
    $attr = ' aria-describedby="fehler-' . e(str_replace('.', '-', $feld)) . '"';
    if (isset($fehler[$feld])) {
        $attr .= ' aria-invalid="true"';
    }
    return $attr;
};
$fehlerAusgabe = static function (string $feld) use ($fehler): string {
    $id = 'fehler-' . str_replace('.', '-', $feld);
    $text = $fehler[$feld] ?? '';
    return '<p class="feld-fehler" id="' . e($id) . '"' . ($text === '' ? ' hidden' : '') . '>' . e($text) . '</p>';
};
$minDatum = heute()->format('Y-m-d');
$anzahlPersonen = count($formular['personen']);
?>
<form method="post" action="<?= e($aktionUrl) ?>" class="anmeldeformular" id="anmeldeformular" novalidate>
    <?= csrfFeld() ?>
    <?= honeypotFeld() ?>
    <?php /* Standard-Button für „Enter“ im Textfeld, damit nicht versehentlich eine Person entfernt wird */ ?>
    <button type="submit" name="aktion" value="speichern" class="standard-button" tabindex="-1" aria-hidden="true">Speichern</button>

    <fieldset class="karte">
        <legend>Einrichtung</legend>
        <div class="raster raster-2">
            <div class="feld<?= isset($fehler['einrichtung_name']) ? ' hat-fehler' : '' ?>">
                <label for="einrichtung_name">Name der Einrichtung</label>
                <input type="text" id="einrichtung_name" name="einrichtung_name" required maxlength="200"
                       autocomplete="organization" value="<?= e($formular['einrichtung_name']) ?>"<?= $feldAttr('einrichtung_name') ?>>
                <?= $fehlerAusgabe('einrichtung_name') ?>
            </div>
            <div class="feld<?= isset($fehler['einrichtung_art']) ? ' hat-fehler' : '' ?>">
                <label for="einrichtung_art">Art der Einrichtung</label>
                <select id="einrichtung_art" name="einrichtung_art" required<?= $feldAttr('einrichtung_art') ?>>
                    <option value=""<?= $formular['einrichtung_art'] === '' ? ' selected' : '' ?>>Bitte wählen …</option>
                    <?php foreach (EINRICHTUNG_ARTEN as $wert => $label): ?>
                    <option value="<?= e($wert) ?>"<?= $formular['einrichtung_art'] === $wert ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $fehlerAusgabe('einrichtung_art') ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="karte">
        <legend>Termin und Anlass</legend>
        <div class="raster raster-2">
            <div class="feld<?= isset($fehler['datum']) ? ' hat-fehler' : '' ?>">
                <label for="datum">Tag</label>
                <input type="date" id="datum" name="datum" required min="<?= e($minDatum) ?>"
                       value="<?= e($formular['datum']) ?>"<?= $feldAttr('datum') ?>>
                <?= $fehlerAusgabe('datum') ?>
            </div>
            <div class="feld<?= isset($fehler['uhrzeit']) ? ' hat-fehler' : '' ?>">
                <label for="uhrzeit">Uhrzeit <span class="hinweis-inline">(HH:MM)</span></label>
                <input type="time" id="uhrzeit" name="uhrzeit" required pattern="([01][0-9]|2[0-3]):[0-5][0-9]"
                       placeholder="HH:MM" value="<?= e($formular['uhrzeit']) ?>"<?= $feldAttr('uhrzeit') ?>>
                <?= $fehlerAusgabe('uhrzeit') ?>
            </div>
        </div>
        <div class="feld<?= isset($fehler['betreff']) ? ' hat-fehler' : '' ?>">
            <label for="betreff">Betreff</label>
            <input type="text" id="betreff" name="betreff" required maxlength="255"
                   value="<?= e($formular['betreff']) ?>"<?= $feldAttr('betreff') ?>>
            <?= $fehlerAusgabe('betreff') ?>
        </div>
    </fieldset>

    <fieldset class="karte">
        <legend>Ansprechperson</legend>
        <div class="raster raster-2">
            <div class="feld<?= isset($fehler['ansprech_vorname']) ? ' hat-fehler' : '' ?>">
                <label for="ansprech_vorname">Vorname</label>
                <input type="text" id="ansprech_vorname" name="ansprech_vorname" required maxlength="100"
                       autocomplete="given-name" value="<?= e($formular['ansprech_vorname']) ?>"<?= $feldAttr('ansprech_vorname') ?>>
                <?= $fehlerAusgabe('ansprech_vorname') ?>
            </div>
            <div class="feld<?= isset($fehler['ansprech_nachname']) ? ' hat-fehler' : '' ?>">
                <label for="ansprech_nachname">Nachname</label>
                <input type="text" id="ansprech_nachname" name="ansprech_nachname" required maxlength="100"
                       autocomplete="family-name" value="<?= e($formular['ansprech_nachname']) ?>"<?= $feldAttr('ansprech_nachname') ?>>
                <?= $fehlerAusgabe('ansprech_nachname') ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="karte">
        <legend>Externe Personen</legend>
        <p class="hinweis">Einrichtung, Termin, Betreff und Ansprechperson gelten für alle Personen dieser Anmeldung.</p>
        <ol class="personen-liste" id="personen-liste">
            <?php foreach ($formular['personen'] as $i => $name): $schluessel = 'personen.' . $i; ?>
            <li class="person feld<?= isset($fehler[$schluessel]) ? ' hat-fehler' : '' ?>">
                <label for="person-<?= $i ?>">Name der externen Person <span class="person-nr"><?= $i + 1 ?></span></label>
                <div class="person-zeile">
                    <input type="text" id="person-<?= $i ?>" name="personen[]" required maxlength="200"
                           value="<?= e($name) ?>"<?= $feldAttr($schluessel) ?>>
                    <button type="submit" name="aktion" value="person_entfernen_<?= $i ?>" formnovalidate
                            class="button button-entfernen"<?= $anzahlPersonen < 2 ? ' hidden' : '' ?>
                            aria-label="Person <?= $i + 1 ?> entfernen">Entfernen</button>
                </div>
                <?= $fehlerAusgabe($schluessel) ?>
            </li>
            <?php endforeach; ?>
        </ol>
        <button type="submit" name="aktion" value="person_hinzufuegen" formnovalidate class="button button-sekundaer" id="person-hinzufuegen">
            + Weitere Person hinzufügen
        </button>
    </fieldset>

    <section class="karte datenschutz" aria-labelledby="datenschutz-titel">
        <h2 id="datenschutz-titel">Datenschutzhinweis</h2>
        <?php foreach (datenschutzAbsaetze() as $absatz): ?>
        <p><?= nl2br(e($absatz)) ?></p>
        <?php endforeach; ?>
    </section>

    <div class="aktionen">
        <button type="submit" name="aktion" value="speichern" class="button button-primaer"><?= e($absendenText) ?></button>
    </div>
</form>
