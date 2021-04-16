# REST API Aplikasi Antrean Puskesmas Babatan (Lumen)

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

Repository ini merupakan project REST API untuk penyedia alur komunikasi data antara Database dan Aplikasi pengguna (Mobile / Web). Dikembangkan dengan Framework Laravel/Lumen PHP. 

## Installation
- `git clone "https://github.com/KoTA107-18/REST-API-Babatan.git"`
- `cd REST-API-Babatan`
- `composer update`
- `php artisan swagger-lume:publish`
- `php artisan swagger-lume:generate`
- `php -S localhost:8080 public/index.php`
- open `http://localhost:8080/api/documentation` on your browser.

## Official Documentation Lumen

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Contributing

Thank you for considering contributing to Lumen! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
