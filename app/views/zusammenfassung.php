<?php /** @var array $a */ ?>
<section class="karte" aria-labelledby="zf-titel">
<h2 class="karte-titel" id="zf-titel">Zusammenfassung</h2>
<dl class="zf">
<div><dt>Einrichtung</dt><dd><?= e(einrichtung_mit_art($a)) ?></dd></div>
<div><dt>Termin</dt><dd><?= e(termin_lang(new DateTimeImmutable($a['besuch_am']))) ?></dd></div>
<div><dt>Betreff</dt><dd><?= e($a['betreff']) ?></dd></div>
<div><dt>Ansprechperson</dt><dd><?= e($a['ansprech_vorname'] . ' ' . $a['ansprech_nachname']) ?></dd></div>
<div class="zf-voll">
<dt>Externe Personen</dt>
<dd><ul class="chips">
<?php foreach ($a['personen'] as $p): ?>
<li><?= e($p['name']) ?></li>
<?php endforeach; ?>
</ul></dd>
</div>
</dl>
<p class="zf-fuss">Alle Daten werden 48 Stunden nach dem Besuchstermin automatisch gelöscht.</p>
</section>
