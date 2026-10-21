<?php /** @var array $anmeldung @var string $bearbeitenUrl */ ?>
<div class="meldung meldung-erfolg" role="status">Vielen Dank! Der Besuch wurde beim Pfortendienst angemeldet.</div>

<section class="karte">
    <h1>Ihr persönlicher Bearbeitungslink</h1>
    <p><strong>Bitte speichern Sie diesen Link</strong> (z. B. als Lesezeichen oder in Ihren Notizen). Nur über diesen Link können Sie die Anmeldung später ändern oder absagen. Aus Datenschutzgründen wird der Link nirgends sonst gespeichert und kann nach dem Schließen des Browsers nicht wiederhergestellt werden.</p>
    <div class="link-box">
        <input type="text" id="bearbeiten-link" value="<?= e($bearbeitenUrl) ?>" readonly aria-label="Bearbeitungslink">
        <button type="button" class="button button-sekundaer" id="link-kopieren" hidden>Link kopieren</button>
    </div>
    <p class="hinweis" id="kopier-status" role="status"></p>
    <p><a href="<?= e($bearbeitenUrl) ?>">Anmeldung jetzt ansehen oder ändern</a></p>
</section>

<?php require APP_ROOT . '/templates/zusammenfassung.php'; ?>

<p><a class="button button-sekundaer" href="index.php">Weiteren Besuch anmelden</a></p>
