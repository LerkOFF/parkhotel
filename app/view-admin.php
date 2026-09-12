<?php
$editing = $adminPage !== 'requests' ? page_by_slug($adminPage) : null;
$statusLabels = ['new' => 'Новая', 'confirmed' => 'Подтверждена', 'cancelled' => 'Отменена'];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Админка Парк-Отеля</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-page">
<aside class="admin-sidebar">
    <div class="admin-title"><span class="admin-logo">П</span><b>Парк-Отель</b></div>
    <nav>
        <a class="<?= $adminPage === 'requests' ? 'active' : '' ?>" href="/admin">Заявки <span><?= count(array_filter($requests, fn($r) => $r['status'] === 'new')) ?></span></a>
        <p>Страницы</p>
        <?php foreach (all_pages() as $item): ?>
            <a class="<?= $adminPage === $item['slug'] ? 'active' : '' ?>" href="/admin?page=<?= e($item['slug']) ?>"><?= e($item['h1']) ?></a>
        <?php endforeach; ?>
    </nav>
    <a class="logout" href="/admin/logout">Выйти</a>
</aside>
<main class="admin-main">
    <?php if (isset($_GET['saved'])): ?><div class="saved">Изменения сохранены</div><?php endif; ?>
    <?php if ($editing): ?>
        <div class="admin-heading">
            <div><p>Редактирование страницы</p><h1><?= e($editing['h1']) ?></h1></div>
            <a href="<?= $editing['slug'] === 'home' ? '/' : '/' . $editing['slug'] . '/' ?>" target="_blank">Открыть страницу</a>
        </div>
        <form class="editor" method="post">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="slug" value="<?= e($editing['slug']) ?>">
            <label>Title<input name="title" value="<?= e($editing['title']) ?>" required></label>
            <label>Description<textarea name="description" rows="2" required><?= e($editing['description']) ?></textarea></label>
            <label>H1<input name="h1" value="<?= e($editing['h1']) ?>" required></label>
            <label>Вводный текст<textarea name="intro" rows="3" required><?= e($editing['intro']) ?></textarea></label>
            <div class="editor-field">
                <span class="field-label">Основной текст</span>
                <div class="editor-toolbar" role="toolbar" aria-label="Форматирование текста">
                    <button type="button" data-command="formatBlock" data-value="p">Текст</button>
                    <button type="button" data-command="formatBlock" data-value="h2">Заголовок</button>
                    <button type="button" data-command="formatBlock" data-value="h3">Подзаголовок</button>
                    <button type="button" data-command="bold"><b>Ж</b></button>
                    <button type="button" data-command="italic"><i>К</i></button>
                    <button type="button" data-command="insertUnorderedList">Список</button>
                    <button type="button" data-command="createLink">Ссылка</button>
                    <button type="button" data-command="removeFormat">Очистить</button>
                    <label class="upload-button">Фото или файл<input class="editor-upload" type="file" accept="image/jpeg,image/png,image/webp,image/gif,.pdf,.doc,.docx,.xls,.xlsx"></label>
                </div>
                <div class="rich-editor" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Введите текст страницы…"><?= sanitize_content_html($editing['body']) ?></div>
                <textarea class="editor-html" name="body"><?= e($editing['body']) ?></textarea>
                <p class="upload-status" aria-live="polite"></p>
                <p class="editor-hint">Можно добавлять заголовки, списки, ссылки, фотографии, PDF, Word и Excel. До 10 МБ на файл.</p>
            </div>
            <button name="save_page" value="1">Сохранить</button>
        </form>
    <?php else: ?>
        <div class="admin-heading"><div><p>Обращения с сайта</p><h1>Заявки</h1></div></div>
        <?php if (!$requests): ?>
            <div class="empty"><h2>Пока нет заявок</h2><p>Новые обращения появятся здесь после отправки формы на сайте.</p></div>
        <?php else: ?>
            <div class="request-list">
                <?php foreach ($requests as $request): ?>
                    <article>
                        <header><div><span class="status status-<?= e($request['status']) ?>"><?= e($statusLabels[$request['status']] ?? 'Новая') ?></span><h2><?= e($request['type']) ?></h2></div><time><?= e(date('d.m.Y H:i', strtotime($request['created_at']))) ?></time></header>
                        <dl><div><dt>Имя</dt><dd><?= e($request['name']) ?></dd></div><div><dt>Телефон</dt><dd><a href="tel:<?= e($request['phone']) ?>"><?= e($request['phone']) ?></a></dd></div></dl>
                        <form method="post">
                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                            <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                            <select name="status">
                                <?php foreach ($statusLabels as $value => $label): ?><option value="<?= $value ?>" <?= $request['status'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                            </select>
                            <button name="request_status" value="1">Обновить</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php if ($editing): ?><script>window.PARKHOTEL_EDITOR = {csrf: <?= json_encode(csrf_token()) ?>};</script><script src="/assets/js/admin.js" defer></script><?php endif; ?>
</body>
</html>
