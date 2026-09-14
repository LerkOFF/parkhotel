<?php
$nav = [
    '/nomera/' => 'Номера', '/basseyn/' => 'Бассейн', '/sauna-banya/' => 'Баня',
    '/slavyanka/' => 'Славянка', '/banketnyy-zal/' => 'Банкеты', '/kontakty/' => 'Контакты',
];
$serviceImage = match ($slug) {
    'nomera' => '/assets/images/room-double.webp',
    'basseyn' => '/assets/images/pool.webp',
    'sauna-banya' => '/assets/images/sauna.webp',
    'banketnyy-zal' => '/assets/images/banquet-hall.webp',
    'arenda' => '/assets/images/rental.webp',
    'slavyanka', 'pominalnye-obedy' => '/assets/images/slavyanka-dining.webp',
    default => '/assets/images/hotel-exterior.webp',
};
$serviceAlt = match ($slug) {
    'nomera' => 'Двухместный номер Парк-Отеля',
    'basseyn' => 'Крытый бассейн Парк-Отеля',
    'sauna-banya' => 'Сауна в банном комплексе',
    'banketnyy-zal' => 'Банкетный зал во время мероприятия',
    'arenda' => 'Помещение для аренды',
    'slavyanka' => 'Сервировка стола в «Славянке»',
    'pominalnye-obedy' => 'Сервировка для мероприятия',
    default => 'Корпус Парк-Отеля на улице Кирова',
};
$pageGallery = match ($slug) {
    'nomera' => [
        ['/assets/images/room-bath.webp', 'Санузел номера'],
        ['/assets/images/room-shower.webp', 'Душ в номере'],
        ['/assets/images/breakfast-menu.webp', 'Завтраки на выбор'],
    ],
    'basseyn' => [
        ['/assets/images/pool-hall.webp', 'Чаша крытого бассейна'],
        ['/assets/images/sauna.webp', 'Вход в сауну'],
    ],
    'sauna-banya' => [
        ['/assets/images/banya.webp', 'Комната отдыха банного комплекса'],
        ['/assets/images/pool.webp', 'Бассейн рядом с баней'],
    ],
    'banketnyy-zal' => [
        ['/assets/images/banquet-night.webp', 'Банкетный павильон вечером'],
        ['/assets/images/slavyanka-table.webp', 'Сервировка банкетного стола'],
    ],
    'slavyanka' => [
        ['/assets/images/slavyanka-table.webp', 'Праздничный стол «Славянки»'],
        ['/assets/images/breakfast-menu.webp', 'Завтраки на выбор'],
    ],
    'arenda' => [
        ['/assets/images/banquet-night.webp', 'Павильон для мероприятий'],
        ['/assets/images/banquet-hall.webp', 'Банкетный зал'],
    ],
    'o-nas' => [
        ['/assets/images/pool.webp', 'Крытый бассейн с мозаикой'],
        ['/assets/images/banquet-night.webp', 'Банкетный павильон'],
    ],
    default => [],
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
        <img class="brand-mark" src="/assets/images/logo-mark.png" alt="" width="256" height="224"><span>Парк-Отель<small>Вятские Поляны</small></span>
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
        <figure class="hero-image"><img src="/assets/images/hotel-exterior.webp" alt="Корпус Парк-Отеля на улице Кирова в Вятских Полянах" width="604" height="452" fetchpriority="high"><figcaption>Корпус на ул. Кирова, 2А. Фото из группы ВК.</figcaption></figure>
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
            <a class="service-large" href="/nomera/"><img src="/assets/images/room-double.webp" alt="Двухместный номер Парк-Отеля"><span><b>Номера</b>Отдых после дороги и завтрак утром</span></a>
            <a href="/basseyn/"><img src="/assets/images/pool.webp" alt="Крытый бассейн с мозаикой"><span><b>Бассейн и баня</b>Для гостей отеля и жителей города</span></a>
            <a href="/banketnyy-zal/"><img src="/assets/images/banquet-hall.webp" alt="Банкетный зал во время мероприятия"><span><b>Банкетный зал</b>Отдельное пространство до 150 гостей</span></a>
            <a href="/slavyanka/"><img src="/assets/images/slavyanka-dining.webp" alt="Сервировка стола в столовой Славянка"><span><b>Столовая «Славянка»</b>Домашняя кухня и зал на 50 мест</span></a>
        </div>
    </section>

    <section class="wellness section">
        <div><h2>Восстановить силы</h2><p>Поплавайте, прогрейтесь в сауне или забронируйте русскую баню.</p><a class="text-link" href="/basseyn/">Узнать о бассейне</a></div>
        <img src="/assets/images/banya.webp" alt="Комната отдыха банного комплекса" loading="lazy">
    </section>
<?php else: ?>
    <section class="inner-hero">
        <div><a class="back-link" href="/">На главную</a><h1><?= e($page['h1']) ?></h1><p><?= e($page['intro']) ?></p></div>
        <figure><img src="<?= $serviceImage ?>" alt="<?= e($serviceAlt) ?>" width="1280" height="960"><figcaption>Фото из группы ВК.</figcaption></figure>
    </section>
    <?php if ($pageGallery): ?>
    <section class="photo-strip" aria-label="Фотографии раздела">
        <?php foreach ($pageGallery as [$src, $alt]): ?>
            <figure><img src="<?= $src ?>" alt="<?= e($alt) ?>" loading="lazy"<?= str_contains($src, 'breakfast') ? ' class="photo-doc"' : '' ?>></figure>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
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
    <div><a class="brand footer-brand" href="/"><img class="brand-mark" src="/assets/images/logo-mark.png" alt="" width="256" height="224"><span>Парк-Отель<small>Вятские Поляны</small></span></a><p>Отдых, здоровье и события рядом с городским парком.</p></div>
    <div><strong>Адрес</strong><p>Кировская область<br>Вятские Поляны, ул. Кирова, 2А</p></div>
    <div><strong>Связаться</strong><p><a href="tel:+78333461129">+7 (83334) 6-11-29</a><br><a href="mailto:canMol@yandex.ru">canMol@yandex.ru</a></p></div>
    <div><strong>Разделы</strong><p><a href="/o-nas/">О нас</a><br><a href="/arenda/">Аренда</a><br><a href="/ryadom-s-otelem/">Что рядом</a></p></div>
</footer>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
