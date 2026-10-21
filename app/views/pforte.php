<?php
/** @var DateTimeImmutable $heute @var array $zeilen @var array $besuche */
$anzahlBesuche = count($besuche);
$anzahlPersonen = count($zeilen);
?>
<div class="liste-kopf">
<div>
<h1 class="titel klein-abstand">Besuche heute</h1>
<p class="liste-datum"><?= e(datum_lang($heute)) ?></p>
<?php if ($zeilen): ?>
<p class="text-klein liste-anzahl"><?= $anzahlBesuche ?> <?= $anzahlBesuche === 1 ? 'Besuch' : 'Besuche' ?> · <?= $anzahlPersonen ?> <?= $anzahlPersonen === 1 ? 'Person' : 'Personen' ?></p>
<?php endif; ?>
</div>
<button type="button" class="btn btn-drucken" data-drucken<?= $zeilen ? '' : ' disabled' ?>>
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 9V3h10v6M7 17H4a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-3"/><rect x="7" y="14" width="10" height="7" rx="1"/></svg>Drucken</button>
</div>

<?php if (!$zeilen): ?>
<section class="karte leer">
<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6E6E73" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>
<h2>Für heute sind keine Besuche angemeldet.</h2>
<p class="text-grau">Neue Anmeldungen für heute erscheinen nach dem Neuladen der Seite.</p>
</section>
<?php else: ?>

<div class="karte tabelle-karte">
<div class="tabelle-scroll">
<table class="liste">
<thead>
<tr>
<th scope="col">Einrichtung</th>
<th scope="col">Tag</th>
<th scope="col">Uhrzeit</th>
<th scope="col">Name der externen Person</th>
<th scope="col">Betreff</th>
<th scope="col">Ansprechperson</th>
</tr>
</thead>
<tbody>
<?php $vorher = null; foreach ($zeilen as $z): $am = new DateTimeImmutable($z['besuch_am']); ?>
<tr class="<?= $vorher === $z['id'] ? 'folge' : 'neu' ?>">
<td><?= e(einrichtung_mit_art($z)) ?></td>
<td class="nowrap"><?= e(datum_kurz($am)) ?></td>
<td class="nowrap zeit"><?= e($am->format('H:i')) ?></td>
<td class="name"><?= e($z['name']) ?></td>
<td><?= e($z['betreff']) ?></td>
<td><?= e($z['ansprech_vorname'] . ' ' . $z['ansprech_nachname']) ?></td>
</tr>
<?php $vorher = $z['id']; endforeach; ?>
</tbody>
</table>
</div>
</div>

<ul class="besuch-karten">
<?php foreach ($besuche as $b): $n = count($b['personen']); ?>
<li class="besuch-karte">
<div class="besuch-karte-kopf">
<span class="besuch-zeit"><?= e((new DateTimeImmutable($b['besuch_am']))->format('H:i')) ?></span>
<span class="besuch-einrichtung"><?= e(einrichtung_mit_art($b)) ?></span>
</div>
<dl>
<div><dt><?= $n > 1 ? 'Personen (' . $n . ')' : 'Person' ?></dt><dd class="name"><?= e(implode(', ', $b['personen'])) ?></dd></div>
<div><dt>Betreff</dt><dd><?= e($b['betreff']) ?></dd></div>
<div><dt>Ansprechperson</dt><dd><?= e($b['ansprech_vorname'] . ' ' . $b['ansprech_nachname']) ?></dd></div>
</dl>
</li>
<?php endforeach; ?>
</ul>

<?php endif; ?>
