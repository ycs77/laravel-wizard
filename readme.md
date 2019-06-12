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

### 1. Generate controller and wizard steps

Now you can quickly generate the wizard controller and the wizard steps:

```bash
php artisan make:wizard UserSetup UsernameStep,PhoneStep
```

This command generate the `UserSetupWizardController`, `UsernameStep`, `PhoneStep` class, and append the wizard route to `routes/web.php`.

*routes/web.php*
```php
...

Wizard::routes('wizard/user', 'UserWizardController', 'wizard.user');
```

> If you can't use auto append route, you can set `config/wizard.php` attribute `append_route` to `false`.

### 2. Set steps

This is generated UsernameStep class, you can to `rules` method set form validation, and save `$data` to your database via the `saveData` method:

```php
<?php

namespace App\Steps\User;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Ycs77\LaravelWizard\Step;

class UsernameStep extends Step
{
    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug = 'username';

    /**
     * The step show label text.
     *
     * @var string
     */
    protected $label = 'Username';

    /**
     * The step form view path.
     *
     * @var string
     */
    protected $view = 'steps.user.username';

    /**
     * Save this step form data.
     *
     * @param  array|null  $data
     * @return void
     */
    public function saveData($data = null)
    {
        $data = Arr::except($data, 'username');
        Auth::user()->update($data);
    }

    /**
     * Validation rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'username' => 'required|string|min:4|max:20|regex:/^[A-Za-z0-9]+$/',
        ];
    }
}
```

And add steps view, for example:

*resources/views/steps/user_setup/username.blade.php*
```php
<div class="form-group">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" value="{{ old('username') ?? $step->data('username') }}">
    @if ($errors->has('username'))
        <span class="invalid-feedback">{{ $errors->first('username') }}</span>
    @endif
</div>
```

*resources/views/steps/user_setup/phone.blade.php*
```php
<div class="form-group">
    <label for="phone">Phone</label>
    <input type="tel" name="phone" id="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') ?? $step->data('phone') }}">
    @if ($errors->has('phone'))
        <span class="invalid-feedback">{{ $errors->first('phone') }}</span>
    @endif
</div>
```

Next, browse the URL `/wizard/user_setup`, start use the Laravel Wizard.

### 3. Use wizard steps CSS package

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
php artisan make:wizard:controller UserSetupController steps=UsernameStep,PhoneStep
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
