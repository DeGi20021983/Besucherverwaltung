<?php
/** @var string $title @var string $inhalt @var string|null $bereich @var bool|null $breit */
$breit = !empty($breit);
$bereich = $bereich ?? (string) config('hausname', '');
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Besuchsanmeldung</title>
<link rel="stylesheet" href="assets/app.css">
<link rel="stylesheet" href="assets/print.css" media="print">
<script src="assets/app.js" defer></script>
</head>
<body>
<header class="kopf">
<div class="kopf-innen<?= $breit ? ' breit' : '' ?>">
<span class="kopf-titel">Besuchsanmeldung</span>
<?php if ($bereich !== ''): ?><span class="kopf-bereich"><?= e($bereich) ?></span><?php endif; ?>
</div>
</header>
<main class="seite<?= $breit ? ' breit' : '' ?>">
<?= $inhalt ?>
</main>
</body>
</html>
