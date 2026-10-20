<?php
/**
 * Ein Eingabefeld für eine externe Person.
 * Ohne JavaScript entfernt der Button die Person per Formular-Absendung,
 * mit JavaScript direkt im Browser.
 *
 * @var int $i @var string $name @var string|null $fehler @var int $anzahl
 */
$nr = $i + 1;
$id = 'person-' . $nr;
?>
<div class="feld person" data-person>
<label class="lbl" for="<?= e($id) ?>"><?= $anzahl > 1 ? 'Name der externen Person ' . $nr : 'Name der externen Person' ?></label>
<div class="person-zeile">
<div class="person-eingabe">
<input class="inp" id="<?= e($id) ?>" name="personen[]" type="text" maxlength="200" autocomplete="off" required
 placeholder="Vor- und Nachname" value="<?= e($name) ?>"<?= feld_attr($fehler, 'e-' . $id) ?>>
<?= feld_fehler($fehler, 'e-' . $id) ?>
</div>
<button type="submit" class="ibtn" name="aktion" value="person_entfernen_<?= $i ?>" formnovalidate
 aria-label="Person <?= $nr ?> entfernen" data-person-entfernen<?= $anzahl > 1 ? '' : ' hidden' ?>>
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v6M14 11v6"/></svg>
</button>
</div>
</div>
