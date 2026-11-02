<?php
declare(strict_types=1);

// カテゴリの管理（指示書 5-4）：追加・名前の変更・並べ替え・削除（使っている事例がある時は削除できない）。
/** @var array $user */

$pdo = db();
$axes = ['biz' => '業態', 'work' => '工事の種類'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = (string)($_POST['op'] ?? '');
    $catId = (int)($_POST['id'] ?? 0);
    $st = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $st->execute([$catId]);
    $cat = $st->fetch() ?: null;

    if ($op === 'add') {
        $axis = isset($axes[$_POST['axis'] ?? '']) ? $_POST['axis'] : 'biz';
        $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 100);
        if ($name === '') {
            flash('error', '追加する' . $axes[$axis] . 'の名前を入れてください。');
            redirect(admin_url('categories/') . '#axis-' . $axis);
        }
        $dup = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE axis = ? AND name = ?');
        $dup->execute([$axis, $name]);
        if ($dup->fetchColumn() > 0) {
            flash('error', '「' . $name . '」はもうあります。');
            redirect(admin_url('categories/') . '#axis-' . $axis);
        }
        // 絞り込みのアドレス（?biz=… の部分）。名前から作り、重なれば数字を付ける
        $base = make_slug($name) ?: 'cat';
        $slug = $base;
        $chk = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE axis = ? AND slug = ?');
        for ($i = 2; $chk->execute([$axis, $slug]) && $chk->fetchColumn() > 0; $i++) {
            $slug = $base . '-' . $i;
        }
        $max = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM categories WHERE axis = ?');
        $max->execute([$axis]);
        $pdo->prepare('INSERT INTO categories (axis, name, slug, sort_order) VALUES (?, ?, ?, ?)')->execute([$axis, $name, $slug, (int)$max->fetchColumn()]);
        flash('ok', '「' . $name . '」を' . $axes[$axis] . 'に追加しました。');
        redirect(admin_url('categories/') . '#axis-' . $axis);
    }

    if (!$cat) {
        redirect(admin_url('categories/'));
    }
    $back = admin_url('categories/') . '#cat-' . $cat['id'];

    if ($op === 'rename') {
        $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 100);
        if ($name === '') {
            flash('error', '名前を空にはできません。');
        } elseif ($name !== $cat['name']) {
            $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?')->execute([$name, $cat['id']]);
            flash('ok', '「' . $cat['name'] . '」を「' . $name . '」に変えました。');
        }
        redirect($back);
    }

    if ($op === 'up' || $op === 'down') {
        $st = $pdo->prepare('SELECT id FROM categories WHERE axis = ? ORDER BY sort_order, id');
        $st->execute([$cat['axis']]);
        $ids = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
        $i = array_search((int)$cat['id'], $ids, true);
        $j = $op === 'up' ? $i - 1 : $i + 1;
        if (isset($ids[$j])) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            $up = $pdo->prepare('UPDATE categories SET sort_order = ? WHERE id = ?');
            foreach ($ids as $n => $cid) {
                $up->execute([$n + 1, $cid]);
            }
        }
        redirect($back);
    }

    if ($op === 'delete') {
        $st = $pdo->prepare("SELECT COUNT(*) FROM case_categories cc JOIN cases c ON c.id = cc.case_id WHERE cc.category_id = ? AND c.status <> 'deleted'");
        $st->execute([$cat['id']]);
        $used = (int)$st->fetchColumn();
        if ($used > 0) {
            flash('error', '「' . $cat['name'] . '」は' . $used . '件の施工事例で使っているため、削除できません。先に各事例のカテゴリを変えてください。');
            redirect($back);
        }
        $pdo->prepare('DELETE FROM case_categories WHERE category_id = ?')->execute([$cat['id']]); // 削除済みの事例に残った分
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$cat['id']]);
        flash('ok', '「' . $cat['name'] . '」を削除しました。');
        redirect(admin_url('categories/') . '#axis-' . $cat['axis']);
    }
    redirect(admin_url('categories/'));
}

$list = ['biz' => [], 'work' => []];
foreach ($pdo->query("SELECT cat.*, (SELECT COUNT(*) FROM case_categories cc JOIN cases c ON c.id = cc.case_id WHERE cc.category_id = cat.id AND c.status <> 'deleted') AS used
    FROM categories cat ORDER BY axis, sort_order, id") as $r) {
    $list[$r['axis']][] = $r;
}
admin_render('categories', ['user' => $user, 'nav' => 'categories', 'title' => 'カテゴリ', 'axes' => $axes, 'list' => $list, 'flash' => take_flash()]);
