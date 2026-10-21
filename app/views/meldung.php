<?php /** @var string $titel @var string $text @var array|null $link */ ?>
<section class="karte karte-zentriert">
<h1 class="titel-mittel"><?= e($titel) ?></h1>
<p class="text-grau"><?= e($text) ?></p>
<?php if ($link): ?>
<p class="abstand-oben"><a class="btn2" href="<?= e($link['href']) ?>"><?= e($link['text']) ?></a></p>
<?php endif; ?>
</section>
