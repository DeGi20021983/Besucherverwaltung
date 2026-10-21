<?php /** @var array $a @var string $link @var string $bearbeiten */ ?>
<div class="kopf-zentriert">
<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#1B7F3B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M7.5 12.5l3 3 6-7"/></svg>
<h1 class="titel">Anmeldung gespeichert</h1>
<p class="lead">Der Pfortendienst sieht den Besuch am Besuchstag in der Tagesliste.</p>
</div>

<div class="stapel-gross">

<section class="karte" aria-labelledby="link-titel">
<h2 class="karte-titel klein-abstand" id="link-titel">Ihr persönlicher Bearbeitungslink</h2>
<div class="alert alert-warnung">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l10 18H2L12 3z"/><path d="M12 10v5M12 18v.5"/></svg>
<p><strong>Bitte speichern Sie diesen Link</strong>, zum Beispiel als Lesezeichen. Nur damit können Sie die Anmeldung später ändern oder absagen.</p>
</div>
<label class="lbl" for="bearbeitungslink">Link zur Anmeldung</label>
<div class="link-zeile">
<input class="inp inp-mono" id="bearbeitungslink" type="text" readonly value="<?= e($link) ?>">
<button type="button" class="btn2" data-kopieren="bearbeitungslink" hidden>Link kopieren</button>
</div>
<div class="aktionen">
<a href="<?= e($bearbeiten) ?>" class="btn">Anmeldung bearbeiten</a>
<a href="index.php" class="link-gross">Weiteren Besuch anmelden</a>
</div>
</section>

<?= render('zusammenfassung', ['a' => $a]) ?>

</div>
