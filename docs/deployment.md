# Развёртывание

## Текущее окружение

Продакшен: панель `tabsonru`, домены `https://parkhotelvp.ru` и `https://parkhotelvp.online`, PHP 8.3 LSAPI, FTP `lerk`, корень `/www/parkhotelvp.ru/`. IP `5.253.61.98` и `5.253.61.102`. Let’s Encrypt включён, `www` режется на apex, HTTP на HTTPS. Хеш админки: `var/admin_password_hash`.

Превью: `https://parkhotel.82-39-215-202.nip.io` на `max_bot`, код `/var/www/parkhotel`, nginx, PHP-FPM 8.3, хеш `/etc/parkhotel/admin_password_hash`.

Репозиторий: `git@github.com:LerkOFF/parkhotel.git`, ветка `main`.

## Первичное развёртывание

Код на сервере появляется только через `git clone`. После клонирования создаются `var/` и `uploads/`, владельцем каталогов становится `www-data`, конфигурация nginx проверяется через `nginx -t`, затем nginx перечитывается. Для редактора нужны пакеты `php8.3-xml` и `php8.3-common` (DOM и Fileinfo).

Пароль админки не хранится в репозитории. На сервере создаётся файл `/etc/parkhotel/admin_password_hash`, доступный root и группе `www-data`. В nginx в FastCGI передаётся только путь к этому файлу.

Временный адрес закрыт от поисковой индексации заголовком `X-Robots-Tag`. Конфигурация находится в `deploy/nginx-preview.conf` и копируется в nginx после клонирования репозитория.

HTTPS выпущен через Certbot для `parkhotel.82-39-215-202.nip.io`. Сертификат автоматически обновляется системным таймером. Certbot дополняет рабочую копию nginx в `/etc/nginx/sites-available/parkhotel-preview`; файл из репозитория остаётся базовой HTTP-конфигурацией для первичного выпуска сертификата.

## Обновление

Пока код правится в Git: коммит в `main`, `git push origin main`, затем заливка FTP `lerk` в `/www/parkhotelvp.ru/`. Превью на `max_bot` обновляется отдельно: `git pull --ff-only origin main`. Не копировать код SCP/rsync. Не заводить второй FTP.

```bash
ssh max_bot
cd /var/www/parkhotel
git pull --ff-only origin main
php -l index.php
sudo nginx -t
sudo systemctl reload nginx
```

При обычном обновлении не копировать `deploy/nginx-preview.conf` поверх рабочего файла nginx: рабочий файл содержит добавленные Certbot настройки HTTPS. При изменении лимитов загрузки переносить в рабочий конфиг только соответствующие директивы и снова выполнять `nginx -t`.

Миграции выполняются приложением при первом запросе: старые статусы `working` и `done` преобразуются в `confirmed`. Перед изменениями структуры таблиц нужно отдельно сделать резервную копию `var/parkhotel.sqlite`.

## Проверка

- открыть главную и внутреннюю страницу;
- проверить HTTPS и отсутствие смешанного содержимого;
- отправить тестовую заявку;
- убедиться, что она появилась в `/admin`;
- проверить статусы «Новая», «Подтверждена», «Отменена»;
- загрузить через редактор тестовое изображение и документ, проверить их открытие и удалить тестовые загрузки;
- удалить тестовую запись из SQLite после проверки;
- проверить `nginx` и `php8.3-fpm` в `systemctl`;
- проверить `certbot.timer` в `systemctl`;
- проверить журнал `/var/log/nginx/parkhotel.error.log`.

## Целевой хост заказчика

Панель: `https://ru-ru1-srv-shrd-22.adminvps.net/ispmgr`, пользователь `tabsonru`. FTP только `lerk`. Сайты `parkhotelvp.ru` и `parkhotelvp.online` заведены 16 сентября 2026, оба смотрят в `/www/parkhotelvp.ru/`. PHP LSAPI 8.3. `.htaccess` не должен переписывать `/.well-known/`.

Тикет AdminVPS `#205134`: 15 сентября поддержку написала, что услуга активирована; 16 сентября панель `gerbsemi` снова принимала логин. WWW parkhotelvp сняты с leftover `gerbsemi` и созданы у `tabsonru`. `гербсемьи.рф` на `gerbsemi` не трогать. `hotelvp.ru` не трогать.

## Ограничения

- не менять DNS parkhotelvp.ru / parkhotelvp.online на чужой IP;
- не хранить пароль или его открытое значение в Git;
- не включать email, Telegram, MAX и ВК без подтверждённых получателей и ключей;
- не выдавать старые или постановочные кадры из группы ВК за полный актуальный фотонабор объекта;
- не удалять каталог `var/` при обновлении кода.
