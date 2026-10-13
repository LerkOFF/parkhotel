<?php
$nav = [
    '/nomera/' => 'Номера', '/basseyn/' => 'Бассейн', '/sauna-banya/' => 'Баня',
    '/slavyanka/' => 'Славянка', '/banketnyy-zal/' => 'Банкеты', '/kontakty/' => 'Контакты',
];
$documentSlugs = ['politika-konfidencialnosti', 'soglasie-na-obrabotku-pd', 'politika-cookie'];
$isDocument = in_array($slug, $documentSlugs, true);
$serviceImage = match ($slug) {
    'nomera' => '/assets/images/room-double.webp',
    'nomer-s-detskoy-krovatkoy' => '/assets/images/room-crib.webp',
    'basseyn' => '/assets/images/pool.webp',
    'sauna-banya' => '/assets/images/sauna.webp',
    'banketnyy-zal' => '/assets/images/banquet-hall.webp',
    'arenda' => '/assets/images/rental.webp',
    'slavyanka', 'pominalnye-obedy' => '/assets/images/slavyanka-dining.webp',
    default => '/assets/images/hotel-exterior.webp',
};
$serviceAlt = match ($slug) {
    'nomera' => 'Двухместный номер Парк-Отеля',
    'nomer-s-detskoy-krovatkoy' => 'Номер с кроватью King Size и детской кроваткой',
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
        ['/assets/images/room-crib.webp', 'Номер с кроватью King Size и детской кроваткой'],
        ['/assets/images/room-shower-full.webp', 'Душевая в номере'],
        ['/assets/images/room-robes.webp', 'Халаты в шкафу номера'],
        ['/assets/images/room-bath.webp', 'Санузел номера'],
        ['/assets/images/room-shower.webp', 'Душ и дозаторы с шампунем и гелем'],
        ['/assets/images/breakfast-menu.webp', 'Завтраки на выбор'],
    ],
    'nomer-s-detskoy-krovatkoy' => [
        ['/assets/images/room-robes.webp', 'Халаты в шкафу номера'],
        ['/assets/images/room-shower-full.webp', 'Душевая в номере'],
        ['/assets/images/room-bath.webp', 'Санузел номера'],
    ],
    'basseyn' => [
        ['/assets/images/pool-ladder.webp', 'Лестница в чашу бассейна'],
        ['/assets/images/pool-hall.webp', 'Чаша крытого бассейна'],
        ['/assets/images/sauna.webp', 'Вход в сауну'],
    ],
    'sauna-banya' => [
        ['/assets/images/banya.webp', 'Комната отдыха банного комплекса'],
        ['/assets/images/banya-broom.webp', 'Веник, шапка и рукавица в парной'],
        ['/assets/images/sauna-stones.webp', 'Камни в печи парной'],
        ['/assets/images/banya-tea.webp', 'Горячий вятский квас, травяной чай и баранки'],
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
    'nomer-s-detskoy-krovatkoy' => 'Номер с детской кроваткой',
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
    <?= head_icons() ?>
    <link rel="stylesheet" href="/assets/css/site.css">
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org', '@type' => 'Hotel', 'name' => 'Парк-Отель',
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'ул. Кирова, 2А', 'addressLocality' => 'Вятские Поляны', 'addressRegion' => 'Кировская область'],
        'telephone' => '+7 (83334) 6-11-29', 'url' => 'https://parkhotelvp.ru',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body>
<div class="topbar"><div class="topbar-inner"><span>Вятские Поляны, ул. Кирова, 2А</span><a href="mailto:ParkHotelVP@yandex.ru">ParkHotelVP@yandex.ru</a></div></div>
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
        <figure class="hero-image"><img src="/assets/images/hotel-exterior.webp" alt="Корпус Парк-Отеля на улице Кирова в Вятских Полянах" width="604" height="452" fetchpriority="high"></figure>
    </section>

    <section class="facts" aria-label="Преимущества">
        <div><strong>22</strong><span>номеров</span></div>
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

    <section class="seo-text section" aria-labelledby="seo-title">
        <h2 id="seo-title">Гостиница в Вятских Полянах: Парк-Отель</h2>
        <div class="seo-columns">
            <div>
                <h3>Где остановиться в Вятских Полянах</h3>
                <p>Парк-Отель — гостиница в центре Вятских Полян, на улице Кирова, 2А. Она стоит рядом с городским парком, на территории «Вятской здравницы», на месте бывшего профилактория «Молот». Сюда приезжают в командировку, на выходные, всей семьёй и по случаю: на свадьбу, юбилей или поминальный обед. Жители города приходят в бассейн, баню и сауну.</p>
                <h3>Номера с завтраком</h3>
                <p>В отеле 22 номера, и все после нового ремонта. В двухместном номере двуспальная кровать King Size, телевизор, кондиционер, холодильник, Wi-Fi, душевая и отдельный санузел с феном. Гостей ждут халат, тапочки, полотенца и косметические принадлежности, а на этаже стоит кулер с питьевой водой. В стоимость проживания входят завтрак на выбор (каша, яичница или блинчики) и посещение бассейна. Если вы едете с малышом, <a href="/nomer-s-detskoy-krovatkoy/">номер с детской кроваткой</a> можно запросить при бронировании: кроватка у нас одна, поэтому лучше уточнить заранее. Подробнее о <a href="/nomera/">номерах и завтраках</a>.</p>
                <h3>Бассейн, баня и сауна</h3>
                <p>В комплексе есть крытый <a href="/basseyn/">бассейн</a>, сауна и русская <a href="/sauna-banya/">баня</a>. Они открыты не только для постояльцев, но и для жителей Вятских Полян. Баню и сауну лучше записывать заранее: оставьте заявку, и администратор подберёт свободное время для вашей компании.</p>
            </div>
            <div>
                <h3>Столовая и банкетный зал</h3>
                <p>В столовой <a href="/slavyanka/">«Славянка»</a> готовят домашнюю русскую кухню: завтраки для гостей отеля, обеды и блюда для мероприятий. Зал рассчитан на 50 человек. Для больших событий есть <a href="/banketnyy-zal/">банкетный зал на 150 гостей</a>: свадьбы, юбилеи, корпоративы. Отдельно организуем <a href="/pominalnye-obedy/">поминальные обеды</a>: администратор поможет с меню, числом гостей и подготовкой зала.</p>
                <h3>Парковка и аренда помещений</h3>
                <p>У отеля есть парковка для гостей. Кабинеты, залы и другие помещения комплекса можно взять в <a href="/arenda/">аренду</a> для работы или мероприятия. Что находится рядом с отелем, читайте на странице <a href="/ryadom-s-otelem/">«Что рядом»</a>.</p>
                <h3>Как забронировать</h3>
                <p>Оставьте имя и телефон в <a href="#request">форме заявки</a> или позвоните по номеру <a href="tel:+78333461129">+7 (83334) 6-11-29</a>. Администратор уточнит даты, ответит на вопросы и подтвердит бронь. Адрес и другие контакты на странице <a href="/kontakty/">«Контакты»</a>.</p>
            </div>
        </div>
    </section>
<?php elseif ($isDocument): ?>
    <article class="document">
        <a class="back-link" href="/">На главную</a>
        <h1><?= e($page['h1']) ?></h1>
        <p class="document-date"><?= e($page['intro']) ?></p>
        <div class="rich-content"><?= sanitize_content_html($page['body']) ?></div>
    </article>
<?php else: ?>
    <section class="inner-hero">
        <div><a class="back-link" href="/">На главную</a><h1><?= e($page['h1']) ?></h1><p><?= e($page['intro']) ?></p></div>
        <figure><img src="<?= $serviceImage ?>" alt="<?= e($serviceAlt) ?>" width="1280" height="960"></figure>
    </section>
    <?php if ($pageGallery): ?>
    <section class="photo-strip<?= count($pageGallery) % 3 === 0 ? ' photo-strip-thirds' : '' ?>" aria-label="Фотографии раздела">
        <?php foreach ($pageGallery as [$src, $alt]): ?>
            <figure><img src="<?= $src ?>" alt="<?= e($alt) ?>" loading="lazy"<?= str_contains($src, 'breakfast') ? ' class="photo-doc"' : '' ?>></figure>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
    <section class="content-section section"><div><h2>Главное</h2><div class="rich-content"><?= sanitize_content_html($page['body']) ?></div></div><aside><strong>Уточнить у администратора</strong><p>Свободные даты, актуальную стоимость и детали услуги.</p><div class="actions"><a class="button" href="#request">Оставить заявку</a><a class="button button-outline" href="tel:+78333461129">Позвонить</a></div></aside></section>
<?php endif; ?>

<?php if (!$isDocument): ?>
    <section class="request-section section" id="request">
        <div><h2>Уточнить свободное время</h2><p>Оставьте контакты. Администратор свяжется с вами и ответит на вопросы.</p></div>
        <form class="request-form" action="/api/request" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="type" value="<?= e($requestType) ?>">
            <label>Ваше имя<input name="name" autocomplete="name" required></label>
            <label>Телефон<input name="phone" type="tel" autocomplete="tel" required></label>
            <label class="consent wide"><input type="checkbox" required> <span>Согласен на <a href="/soglasie-na-obrabotku-pd/" target="_blank" rel="noopener">обработку персональных данных</a> на условиях <a href="/politika-konfidencialnosti/" target="_blank" rel="noopener">политики</a></span></label>
            <button class="button wide" type="submit">Отправить заявку</button>
            <p class="form-status wide" aria-live="polite"></p>
        </form>
    </section>
<?php endif; ?>
</main>

<footer>
    <div><a class="brand footer-brand" href="/"><img class="brand-mark" src="/assets/images/logo-mark.png" alt="" width="256" height="224"><span>Парк-Отель<small>Вятские Поляны</small></span></a><p>Отдых, здоровье и события рядом с городским парком.</p></div>
    <div><strong>Контакты</strong><p>Кировская область<br>Вятские Поляны, ул. Кирова, 2А<br><a href="tel:+78333461129">+7 (83334) 6-11-29</a><br><a href="mailto:ParkHotelVP@yandex.ru">ParkHotelVP@yandex.ru</a></p></div>
    <div class="footer-menu"><strong>Разделы</strong><p><a href="/nomera/">Номера</a><br><a href="/nomer-s-detskoy-krovatkoy/">Номер с детской кроваткой</a><br><a href="/basseyn/">Бассейн</a><br><a href="/sauna-banya/">Баня и сауна</a><br><a href="/slavyanka/">Славянка</a><br><a href="/banketnyy-zal/">Банкетный зал</a><br><a href="/pominalnye-obedy/">Поминальные обеды</a><br><a href="/arenda/">Аренда</a><br><a href="/o-nas/">О нас</a><br><a href="/ryadom-s-otelem/">Что рядом</a><br><a href="/kontakty/">Контакты</a></p></div>
    <div class="footer-legal">
        <p>ООО «Санаторий-профилакторий «Вятская здравница», ИНН 4307018485, ОГРН 1154307000320</p>
        <p><a href="/politika-konfidencialnosti/">Политика обработки персональных данных</a> · <a href="/soglasie-na-obrabotku-pd/">Согласие на обработку данных</a> · <a href="/politika-cookie/">Файлы cookie</a></p>
    </div>
</footer>
<div class="cookie-banner" role="region" aria-label="Файлы cookie" hidden>
    <p>Сайт использует файлы cookie, чтобы работали формы заявок. Рекламу и аналитику мы не подключаем. <a href="/politika-cookie/">Подробнее</a></p>
    <button type="button" class="button cookie-ok">Понятно</button>
</div>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
