<?php
/** @var array $axes */
/** @var array $list axis => rows(id, name, slug, used) */
$help = ['biz' => 'お店の種類です。施工事例の一覧の絞り込みに、この順で並びます。', 'work' => '行った工事の種類です。施工事例の一覧の絞り込みに、この順で並びます。'];
?>
<div class="bar"><h1 class="ttl">カテゴリ</h1></div>
<p class="note">名前を変えると、すべての施工事例と公開ページに反映されます。使っている事例がある間は削除できません。</p>

<?php foreach ($axes as $axis => $axisLabel): $rows = $list[$axis]; $n = count($rows); ?>
<section class="box cats" id="axis-<?= $axis ?>">
  <h2 class="box__ttl"><?= e($axisLabel) ?><span class="box__count"><?= $n ?>件</span></h2>
  <p class="field__help"><?= e($help[$axis]) ?></p>
  <ol class="cats__list">
<?php foreach ($rows as $i => $c): ?>
    <li class="cat" id="cat-<?= (int)$c['id'] ?>">
      <form method="post" class="cat__form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <span class="cat__no" aria-hidden="true"><?= $i + 1 ?></span>
        <label class="u-sr" for="cat-name-<?= (int)$c['id'] ?>"><?= $i + 1 ?>番目の名前</label>
        <input class="input cat__name" type="text" id="cat-name-<?= (int)$c['id'] ?>" name="name" value="<?= e($c['name']) ?>" maxlength="100" required>
        <span class="cat__used"><?= (int)$c['used'] ?>件で使用</span>
        <span class="cat__btns">
          <button class="abtn abtn--line abtn--sm" type="submit" name="op" value="rename">名前を保存</button>
          <button class="ibtn" type="submit" name="op" value="up"<?= $i === 0 ? ' disabled' : '' ?> aria-label="「<?= e($c['name']) ?>」を上へ"><?= icon('chev', 20, 'ibtn__up') ?></button>
          <button class="ibtn" type="submit" name="op" value="down"<?= $i === $n - 1 ? ' disabled' : '' ?> aria-label="「<?= e($c['name']) ?>」を下へ"><?= icon('chev', 20) ?></button>
<?php if ((int)$c['used'] > 0): ?>
          <button class="ibtn ibtn--danger" type="button" disabled aria-label="使っている事例があるため削除できません" title="使っている事例があるため削除できません"><?= icon('close', 20) ?></button>
<?php else: ?>
          <button class="ibtn ibtn--danger" type="submit" name="op" value="delete" formnovalidate data-confirm="「<?= e($c['name']) ?>」を削除します。よろしいですか？" aria-label="「<?= e($c['name']) ?>」を削除"><?= icon('close', 20) ?></button>
<?php endif; ?>
        </span>
      </form>
    </li>
<?php endforeach; ?>
  </ol>
  <form method="post" class="cats__add">
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="add">
    <input type="hidden" name="axis" value="<?= $axis ?>">
    <label class="field__label" for="add-<?= $axis ?>"><?= e($axisLabel) ?>を追加する</label>
    <div class="inline">
      <input class="input" type="text" id="add-<?= $axis ?>" name="name" maxlength="100" placeholder="新しい名前" required>
      <button class="abtn abtn--primary" type="submit">追加</button>
    </div>
  </form>
</section>
<?php endforeach; ?>
