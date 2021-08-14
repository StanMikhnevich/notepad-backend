<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

# ROCKET MINDS TEST | Notes backend

## Платформа для работы с записками

Реализованы функции:

- Регистрация / Аутентификация / Подтверждение почты.
- Создание записки.
- Настройка приватности.
- Поиск записок по заголовку и тексту.
- Открытие доступа к запискам.

- Просмотр своих записок.
- Просмотр доступных записок.

## Запуск приложения

### 1. Установка зависимостей composer
`composer update`

### 2. Настройка .env
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

### 3. Запуск миграций
`php artisan migrate`

### 4. Установка зависимостей npm
`npm i`

### 5. Установка MailHog
- [Скачать](https://github.com/mailhog/MailHog/releases/tag/v1.0.1)
- Запустить .exe
- Открыть [страницу](http://localhost:8025)
- Ждать появления писем
