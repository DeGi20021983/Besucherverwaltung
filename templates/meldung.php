<?php /** @var string $titel @var string $meldung */ ?>
<section class="karte">
    <h1><?= e($titel) ?></h1>
    <p><?= e($meldung) ?></p>
    <?php if (!empty($zurueckLink)): ?>
    <p><a class="button button-sekundaer" href="<?= e($zurueckLink) ?>">Zur Besucheranmeldung</a></p>
    <?php endif; ?>
</section>
