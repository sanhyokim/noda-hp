<?php
/** @var string $name 店名 */
/** @var string $meta 地域・業態 */
/** @var string $text 内容 */
?>
<figure class="voice">
  <span class="voice__mark" aria-hidden="true">“</span>
  <blockquote class="voice__text"><p><?= e($text) ?></p></blockquote>
  <figcaption class="voice__who"><strong><?= e($name) ?></strong><span><?= e($meta) ?></span></figcaption>
</figure>
