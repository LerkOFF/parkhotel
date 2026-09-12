# Парк-Отель

Локальный прототип сайта `parkhotelvp.ru` на PHP 8 и SQLite.

Репозиторий: `git@github.com:LerkOFF/parkhotel.git`.

## Запуск

```bash
cd /Users/lerk/work/parkhotelvp
PARKHOTEL_ADMIN_PASSWORD='смените-пароль' php -S 127.0.0.1:8080 router.php
```

Сайт: <http://127.0.0.1:8080>

Админка: <http://127.0.0.1:8080/admin>

Если переменная не задана, локальный пароль: `admin123`. На VPS такой пароль использовать нельзя.

Продакшен использует bcrypt-хеш из файла вне репозитория. Инструкция: [docs/deployment.md](docs/deployment.md).

Заявки сохраняются в `var/parkhotel.sqlite`. Для отправки копии на email задайте `PARKHOTEL_NOTIFY_EMAIL`. Интеграции Telegram, MAX и ВК подключаются после получения токенов и согласования каналов.

Изображения в `assets/images` сгенерированы для прототипа и должны быть заменены реальными фотографиями объекта перед публикацией.
