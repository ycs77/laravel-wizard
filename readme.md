# Laravel wizard

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-circleci]][link-circleci]
[![Total Downloads][ico-downloads]][link-downloads]

A web setup wizard for your Laravel application.

> This package is adapted from [smajti1/laravel-wizard](https://github.com/smajti1/laravel-wizard).

## Install

Via Composer:

```bash
composer require ycs77/laravel-wizard
```

Publish config:

```bash
php artisan vendor:publish --tag=wizard-config
```

The this package view is use [Bootstrap 4](https://getbootstrap.com/), but if you don't want to use, you can publish views and translations to custom it:

```bash
php artisan vendor:publish --tag=wizard-resources
```

## Usage

### Generate controller and wizard steps

Now you can quickly generate the wizard controller and the wizard steps:

```bash
php artisan make:wizard UserSetup UsernameStep,PhoneNumberStep
```

This command generate the `UserSetupWizardController`, `UsernameStep`, `PhoneNumberStep` class, and append the wizard route to `routes/web.php`.

> If you can't use auto append route, you can set `config/wizard.php` attribute `append_route` to `false`.

After add step view, and open the browser `/wizard/user_setup`, start use the Laravel Wizard.

### Use wizard steps CSS package

This package is based on the [Bootstrap Steps](https://github.com/ycs77/bootstrap-steps) as the CSS package, use NPM installation to use:

```bash
npm install bootstrap-steps
```

Or use yarn:

```bash
yarn add bootstrap-steps
```

## Commands

**Make controller**:

```bash
php artisan make:wizard:controller UserSetupController steps=UsernameStep,PhoneNumberStep
```

The `make:wizard` and `make:wizard:controller` difference, is `make:wizard` will append route and no confirm generate step.

**Make step**:

```bash
php artisan make:wizard:step UsernameStep
```

Or use options:

```bash
php artisan make:wizard:step UsernameStep --label="Username" --slug=username --view=steps.user.username --wizard=user
```

[ico-version]: https://img.shields.io/packagist/v/ycs77/laravel-wizard.svg?style=flat
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat
[ico-circleci]: https://img.shields.io/circleci/project/github/ycs77/laravel-wizard/master.svg?style=flat
[ico-downloads]: https://img.shields.io/packagist/dt/ycs77/laravel-wizard.svg?style=flat

[link-packagist]: https://packagist.org/packages/ycs77/laravel-wizard
[link-circleci]: https://circleci.com/gh/ycs77/laravel-wizard
[link-downloads]: https://packagist.org/packages/ycs77/laravel-wizard
