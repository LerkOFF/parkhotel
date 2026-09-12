<?php
$nav = [
    '/nomera/' => 'Номера', '/basseyn/' => 'Бассейн', '/sauna-banya/' => 'Баня',
    '/slavyanka/' => 'Славянка', '/banketnyy-zal/' => 'Банкеты', '/kontakty/' => 'Контакты',
];
$serviceImage = match ($slug) {
    'nomera' => '/assets/images/room-double.png',
    'basseyn', 'sauna-banya' => '/assets/images/pool-sauna.png',
    default => '/assets/images/hotel-exterior.png',
};
$requestType = match ($slug) {
    'nomera' => 'Бронирование номера',
    'basseyn' => 'Запись в бассейн',
    'sauna-banya' => 'Запись в баню или сауну',
    'banketnyy-zal' => 'Заявка на банкет',
    'pominalnye-obedy' => 'Поминальный обед',
    'arenda' => 'Аренда помещения',
    default => 'Обратная связь',
};
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page['title']) ?></title>
    <meta name="description" content="<?= e($page['description']) ?>">
    <link rel="stylesheet" href="/assets/css/site.css">
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org', '@type' => 'Hotel', 'name' => 'Парк-Отель',
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'ул. Кирова, 2А', 'addressLocality' => 'Вятские Поляны', 'addressRegion' => 'Кировская область'],
        'telephone' => '+7 (83334) 6-11-29', 'url' => 'https://parkhotelvp.ru',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body>
<header class="site-header">
    <a class="brand" href="/" aria-label="Парк-Отель, главная">
        <span class="brand-mark">П</span><span>Парк-Отель<small>Вятские Поляны</small></span>
    </a>
    <button class="menu-button" type="button" aria-expanded="false" aria-controls="site-nav">Меню</button>
    <nav id="site-nav" aria-label="Основная навигация">
        <?php foreach ($nav as $href => $label): ?><a href="<?= $href ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav>
    <a class="header-phone" href="tel:+78333461129">+7 (83334) 6-11-29</a>
</header>

<main>
<?php if ($slug === 'home'): ?>
    <section class="hero">
        <div class="hero-copy">
            <p class="eyebrow">Вятские Поляны, ул. Кирова, 2А</p>
            <h1><?= e($page['h1']) ?></h1>
            <p><?= e($page['intro']) ?></p>
            <div class="actions"><a class="button" href="#request">Забронировать</a><a class="text-link" href="/nomera/">Посмотреть номера</a></div>
        </div>
        <figure class="hero-image"><img src="/assets/images/hotel-exterior.png" alt="Иллюстрация фасада парк-отеля среди деревьев" width="1536" height="1024" fetchpriority="high"><figcaption>Временная визуализация. Перед публикацией заменим реальным фото.</figcaption></figure>
    </section>

    <section class="facts" aria-label="Преимущества">
        <div><strong>26</strong><span>номеров</span></div>
        <div><strong>Включён</strong><span>завтрак</span></div>
        <div><strong>На месте</strong><span>парковка</span></div>
        <div><strong>Рядом</strong><span>городской парк</span></div>
    </section>

    <section class="services section">
        <div class="section-heading"><h2>Всё для отдыха и событий</h2><div class="rich-content"><?= sanitize_content_html($page['body']) ?></div></div>
        <div class="service-grid">
            <a class="service-large" href="/nomera/"><img src="/assets/images/room-double.png" alt="Временная визуализация двухместного номера"><span><b>Номера</b>Отдых после дороги и завтрак утром</span></a>
            <a href="/basseyn/"><img src="/assets/images/pool-sauna.png" alt="Временная визуализация бассейна и сауны"><span><b>Бассейн и баня</b>Для гостей отеля и жителей города</span></a>
            <a class="service-color" href="/banketnyy-zal/"><span><b>Банкетный зал</b>Отдельное пространство до 150 гостей</span></a>
            <a class="service-line" href="/slavyanka/"><span><b>Столовая «Славянка»</b>Домашняя кухня и зал на 50 мест</span></a>
        </div>
    </section>

    <section class="wellness section">
        <div><h2>Восстановить силы</h2><p>Поплавайте, прогрейтесь в сауне или забронируйте русскую баню.</p><a class="text-link" href="/basseyn/">Узнать о бассейне</a></div>
        <img src="/assets/images/pool-sauna.png" alt="Временная визуализация крытого бассейна и сауны" loading="lazy">
    </section>
<?php else: ?>
    <section class="inner-hero">
        <div><a class="back-link" href="/">На главную</a><h1><?= e($page['h1']) ?></h1><p><?= e($page['intro']) ?></p></div>
        <figure><img src="<?= $serviceImage ?>" alt="Временная визуализация раздела <?= e($page['h1']) ?>" width="1536" height="1024"><figcaption>Изображение для прототипа. Требуется реальная фотография.</figcaption></figure>
    </section>
    <section class="content-section section"><div><h2>Главное</h2><div class="rich-content"><?= sanitize_content_html($page['body']) ?></div></div><aside><strong>Уточнить у администратора</strong><p>Свободные даты, актуальную стоимость и детали услуги.</p><a class="button" href="#request">Оставить заявку</a></aside></section>
<?php endif; ?>

    <section class="request-section section" id="request">
        <div><h2>Уточнить свободное время</h2><p>Оставьте контакты. Администратор свяжется с вами и ответит на вопросы.</p></div>
        <form class="request-form" action="/api/request" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="type" value="<?= e($requestType) ?>">
            <label>Ваше имя<input name="name" autocomplete="name" required></label>
            <label>Телефон<input name="phone" type="tel" autocomplete="tel" required></label>
            <label class="consent wide"><input type="checkbox" required> <span>Согласен на обработку данных для ответа на заявку</span></label>
            <button class="button wide" type="submit">Отправить заявку</button>
            <p class="form-status wide" aria-live="polite"></p>
        </form>
    </section>
</main>

<footer>
    <div><a class="brand footer-brand" href="/"><span class="brand-mark">П</span><span>Парк-Отель<small>Вятские Поляны</small></span></a><p>Отдых, здоровье и события рядом с городским парком.</p></div>
    <div><strong>Адрес</strong><p>Кировская область<br>Вятские Поляны, ул. Кирова, 2А</p></div>
    <div><strong>Связаться</strong><p><a href="tel:+78333461129">+7 (83334) 6-11-29</a><br><a href="mailto:canMol@yandex.ru">canMol@yandex.ru</a></p></div>
    <div><strong>Разделы</strong><p><a href="/o-nas/">О нас</a><br><a href="/arenda/">Аренда</a><br><a href="/ryadom-s-otelem/">Что рядом</a></p></div>
</footer>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
