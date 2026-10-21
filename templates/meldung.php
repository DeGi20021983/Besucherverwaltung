<?php /** @var string $titel @var string $meldung */ ?>
<section class="karte">
    <h1><?= e($titel) ?></h1>
    <p><?= e($meldung) ?></p>
    <?php if (!empty($details)): ?>
    <div class="meldung meldung-info diagnose">
        <p><strong>Diagnose</strong> (nur sichtbar, weil „debug“ in config.php eingeschaltet ist und die Seite auf diesem Rechner geöffnet wurde):</p>
        <p><strong>Lösungshinweis:</strong> <?= e($details['hinweis']) ?></p>
        <p><strong>Fehlermeldung:</strong> <code><?= e($details['fehler']) ?></code></p>
    </div>
    <?php endif; ?>
    <?php if (!empty($zurueckLink)): ?>
    <p><a class="button button-sekundaer" href="<?= e($zurueckLink) ?>">Zur Besucheranmeldung</a></p>
    <?php endif; ?>
</section>
