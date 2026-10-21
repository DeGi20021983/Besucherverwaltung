<?php
/**
 * @var DateTimeImmutable $heute @var DateTimeImmutable $stand
 * @var array $besuche @var string $eigeneUrl
 */
?>
<div class="pforte-kopf">
    <div>
        <h1>Besuche am <?= e(datumLang($heute)) ?></h1>
        <p class="stand">Stand: <?= e($stand->format('d.m.Y, H:i')) ?> Uhr · <?= count($besuche) ?> <?= count($besuche) === 1 ? 'Person' : 'Personen' ?></p>
    </div>
    <div class="pforte-aktionen keine-druckausgabe">
        <a class="button button-sekundaer" href="<?= e($eigeneUrl) ?>">Aktualisieren</a>
        <button type="button" class="button button-primaer" id="drucken">Drucken</button>
    </div>
</div>

<?php if (!$besuche): ?>
<div class="meldung meldung-info">Für heute sind keine Besuche angemeldet.</div>
<?php else: ?>
<div class="tabelle-wrapper">
    <table class="tagesliste">
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
            <?php foreach ($besuche as $b): $termin = new DateTimeImmutable($b['termin']); ?>
            <tr>
                <td data-label="Einrichtung"><?= e($b['einrichtung_name']) ?> (<?= e(einrichtungArtLabel($b['einrichtung_art'])) ?>)</td>
                <td data-label="Tag"><?= e($termin->format('d.m.Y')) ?></td>
                <td data-label="Uhrzeit"><?= e($termin->format('H:i')) ?></td>
                <td data-label="Name" class="spalte-name"><?= e($b['besucher_name']) ?></td>
                <td data-label="Betreff"><?= e($b['betreff']) ?></td>
                <td data-label="Ansprechperson"><?= e($b['ansprech_vorname'] . ' ' . $b['ansprech_nachname']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
