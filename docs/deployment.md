# Развёртывание

## Текущее окружение

| Параметр | Значение |
| --- | --- |
| Репозиторий | `git@github.com:LerkOFF/parkhotel.git` |
| SSH-хост | `max_bot` |
| Код на VPS | `/var/www/parkhotel` |
| Публичный URL | `https://parkhotel.82-39-215-202.nip.io` |
| Веб-сервер | nginx |
| PHP | PHP-FPM 8.3 |
| PHP-модули | `pdo_sqlite`, `sqlite3`, `mbstring`, `dom`, `fileinfo` |
| Данные | `/var/www/parkhotel/var/parkhotel.sqlite` |
| Загрузки | `/var/www/parkhotel/uploads`, владелец `www-data` |
| Секрет админки | bcrypt-хеш вне Git |
| nginx | `/etc/nginx/sites-available/parkhotel-preview` |

## Первичное развёртывание

Код на сервере появляется только через `git clone`. После клонирования создаются `var/` и `uploads/`, владельцем каталогов становится `www-data`, конфигурация nginx проверяется через `nginx -t`, затем nginx перечитывается. Для редактора нужны пакеты `php8.3-xml` и `php8.3-common` (DOM и Fileinfo).

Пароль админки не хранится в репозитории. На сервере создаётся файл `/etc/parkhotel/admin_password_hash`, доступный root и группе `www-data`. В nginx в FastCGI передаётся только путь к этому файлу.

Временный адрес закрыт от поисковой индексации заголовком `X-Robots-Tag`. Конфигурация находится в `deploy/nginx-preview.conf` и копируется в nginx после клонирования репозитория.

HTTPS выпущен через Certbot для `parkhotel.82-39-215-202.nip.io`. Сертификат автоматически обновляется системным таймером. Certbot дополняет рабочую копию nginx в `/etc/nginx/sites-available/parkhotel-preview`; файл из репозитория остаётся базовой HTTP-конфигурацией для первичного выпуска сертификата.

## Обновление

Пока WWW на `tabsonru` не готов, правки сайта идут только через Git: коммит в `main`, `git push origin main`, на `max_bot` в `/var/www/parkhotel` — `git pull --ff-only origin main`. Не копировать код SCP/rsync. FTP `lerk` — после появления `www.parkhotelvp.ru` в панели, тем же деревом.

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

Панель: `https://ru-ru1-srv-shrd-22.adminvps.net/ispmgr`, пользователь `tabsonru`. FTP только `lerk`, отдельного пользователя не заводить. Нужные IP уже на этом аккаунте: `parkhotelvp.ru` → `5.253.61.98`, `parkhotelvp.online` → `5.253.61.102`. PHP для сайта — LSAPI 8.3.

14 сентября 2026 WWW этих имён создать из `tabsonru` нельзя: зона и сайт уже есть на ноде, править их этот пользователь не может. HTTP даёт 403 и редирект на `blocked.adminvps.net`. leftover-логин `gerbsemi` панель и FTP не принимают. Пока имя не освободят, новый код остаётся на `max_bot`. DNS A-записи не менять.

Когда WWW окажется у `tabsonru`: залить код через FTP `lerk` в `/www/parkhotelvp.ru/`, хеш админки положить в `var/admin_password_hash` (не в Git), включить Let’s Encrypt, проверить HTTPS, `/`, `/nomera/`, `/admin` и форму.

## Ограничения

- не менять DNS parkhotelvp.ru / parkhotelvp.online, пока WWW не принадлежит `tabsonru`;
- не хранить пароль или его открытое значение в Git;
- не включать email, Telegram, MAX и ВК без подтверждённых получателей и ключей;
- не выдавать старые или постановочные кадры из группы ВК за полный актуальный фотонабор объекта;
- не удалять каталог `var/` при обновлении кода.
