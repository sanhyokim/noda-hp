<?php
/** @var string $title */
/** @var string $message */
/** @var ?array $back [URL, ラベル] */
?>
<section class="panel panel--narrow">
  <h1 class="ttl"><?= e($title) ?></h1>
  <p class="note"><?= e($message) ?></p>
<?php if (!empty($back)): ?>
  <p class="actions-inline"><a class="abtn abtn--primary" href="<?= e($back[0]) ?>"><?= e($back[1]) ?></a></p>
<?php endif; ?>
</section>
