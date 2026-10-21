<?php
/** @var string $titel @var string $inhalt */
$appName = (string) config('app_name', 'Besucheranmeldung');
$seitenKlasse = $seitenKlasse ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($titel === $appName ? $appName : $titel . ' – ' . $appName) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/print.css" media="print">
</head>
<body class="<?= e($seitenKlasse) ?>">
    <header class="kopf">
        <div class="container kopf-inhalt">
            <span class="logo" aria-hidden="true"></span>
            <span class="app-name"><?= e($appName) ?></span>
        </div>
    </header>
    <main class="container" id="inhalt">
        <?= $inhalt ?>
    </main>
    <?php if (!empty($skript)): ?>
    <script src="assets/js/<?= e($skript) ?>" defer></script>
    <?php endif; ?>
</body>
</html>
