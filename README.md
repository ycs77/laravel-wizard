# Laravel Wizard

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Style CI Build Status][ico-style-ci]][link-style-ci]
[![Total Downloads][ico-downloads]][link-downloads]

A web setup wizard for Laravel application.

> This package is adapted from [smajti1/laravel-wizard](https://github.com/smajti1/laravel-wizard).

![Laravel wizard main image.](docs/laravel-wizard-main-image.jpg)

## Table of Contents

- [Laravel Wizard](#laravel-wizard)
  - [Table of Contents](#table-of-contents)
  - [Version Compatibility](#version-compatibility)
  - [Install](#install)
  - [Usage](#usage)
    - [1. Generate controller and wizard steps](#1-generate-controller-and-wizard-steps)
    - [2. Set steps](#2-set-steps)
    - [3. Install wizard steps CSS package](#3-install-wizard-steps-css-package)
  - [Cache](#cache)
    - [Database Driver](#database-driver)
    - [Disable Cache](#disable-cache)
  - [Controller](#controller)
    - [Setting Configuration](#setting-configuration)
  - [Customize View](#customize-view)
  - [Step](#step)
    - [Get cached data](#get-cached-data)
    - [Step repository](#step-repository)
    - [Upload Files](#upload-files)
    - [Skip step](#skip-step)
    - [Passing data to views](#passing-data-to-views)
    - [Save data on other step](#save-data-on-other-step)
    - [Set relationships model](#set-relationships-model)
  - [Commands](#commands)
  - [Credits](#credits)
  - [License](#license)

## Version Compatibility

 | Laravel Wizard | Laravel          | PHP     |
 | :------------- | :--------------- | :------ |
 | 1.0.x          | 5.5              | ^7.0    |
 | 1.1.x          | ^5.6             | ^7.1.3  |
 | 2.0.x,2.1.x    | ^5.6\|^6.x       | ^7.1.3  |
 | 2.2.x          | ^5.6\|^6.x\|^7.x | ^7.1.3  |
 | 2.3.x          | >=5.6            | >=7.1.3 |

## Install

Via Composer:

```bash
composer require ycs77/laravel-wizard
```

Publish config:

```bash
php artisan vendor:publish --tag=wizard-config
```

This package view is use [Bootstrap 5](https://getbootstrap.com/), but if you don't want to use, can publish views to custom it, see [Customize View](#customize-view):

```bash
php artisan vendor:publish --tag=wizard-views-bs5
```

If you want to use Bootstrap 4, you can publish the Bootstrap 4 views:

```bash
php artisan vendor:publish --tag=wizard-views-bs4
```

## Usage

### 1. Generate controller and wizard steps

Now you can quickly generate the wizard controller and the wizard steps:

```bash
php artisan make:wizard User NameStep,EmailStep
```

This command generate the `UserWizardController`, `NameStep`, `EmailStep` class, and append the wizard route to `routes/web.php`.

*routes/web.php*
```php
use App\Http\Controllers\UserWizardController;
use Illuminate\Support\Facades\Route;
use Ycs77\LaravelWizard\Facades\Wizard;

...

Wizard::routes('wizard/user', UserWizardController::class, 'wizard.user');
```

> If you can't use auto append route, you can set `config/wizard.php` attribute `append_route` to `false`.

### 2. Set steps

This is generated NameStep class, you can to `model` method set the model, to `rules` method set form validation, and save `$data` to your database via the `saveData` method, for example:

*app/Steps/User/NameStep.php*
```php
<?php

namespace App\Steps\User;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Ycs77\LaravelWizard\Step;

class NameStep extends Step
{
    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug = 'name';

    /**
     * The step show label text.
     *
     * @var string
     */
    protected $label = 'Name';

    /**
     * Set the step model instance or the relationships instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null
     */
    public function model(Request $request)
    {
        return User::find(1);
    }

    /**
     * Save this step form data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|null  $data
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null  $model
     * @return void
     */
    public function saveData(Request $request, $data = null, $model = null)
    {
        $data = Arr::only($data, 'name');
        $model->update($data);
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
            'name' => 'required',
        ];
    }
}
```

And add steps view, for example:

*resources/views/steps/user/name.blade.php*
```blade
<div class="form-group mb-3">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') ?? $step->data('name') }}">

    @if ($errors->has('name'))
        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
    @endif
</div>
```

*resources/views/steps/user/email.blade.php*
```blade
<div class="form-group mb-3">
    <label for="email">E-mail</label>
    <input type="email" name="email" id="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') ?? $step->data('email') }}">

    @if ($errors->has('email'))
        <span class="invalid-feedback">{{ $errors->first('email') }}</span>
    @endif
</div>
```

Next, browse the URL `/wizard/user`, start use the Laravel Wizard.

> If you want to get the layout see [Laravel UI alyouts/app.blade.php](https://github.com/laravel/ui/blob/3.x/src/Auth/bootstrap-stubs/layouts/app.stub).

### 3. Install wizard steps CSS package

The CSS for this package default view is based on the [Bootstrap Steps](https://github.com/ycs77/bootstrap-steps), use NPM installation to use:

```bash
npm install bootstrap bootstrap-steps
// or Yarn
yarn add bootstrap bootstrap-steps
```

Import to app.scss file and run `npm run dev` or `yarn run dev`:

*resources/sass/app.scss*
```scss
...

@import '~bootstrap/scss/bootstrap';
@import '~bootstrap-steps/scss/bootstrap-steps';
```

## Cache

### Database Driver

In order to use the `database` wizard cache driver, you will need a database table to hold the wizards cache data. To generate a migration that creates this table, run the `wizard:table` Artisan command:

```
php artisan wizard:table

php artisan migrate
```

### Disable Cache

Set `cache` in `config/wizard.php` to `false` to disable cache input data:

```php
'cache' => false,
```

Or set to your WizardController `wizardOptions` property:

```php
protected $wizardOptions = [
    'cache' => false,
];
```

If disable cache, the data will be saved in the data immediately after each step is sent. If you are afraid to save the data repeatedly, you can hide the Prev button, or use `Model::updateOrCreate()` (https://laravel.com/docs/6.x/eloquent#other-creation-methods).

## Controller

### Setting Configuration

Add `wizardOptions` property to `controller`, you can use `cache`, `driver`, `connection`, `table` options to override configuration.

*app/Http/Controllers/UserWizardController.php*
```php
/**
 * The wizard options.
 *
 * @var array
 */
protected $wizardOptions = [
    'cache' => false,
];
```

## Customize View

First, publish layouts:

```bash
php artisan vendor:publish --tag=wizard-views-bs5
```

Or you can publish Bootstrap 4 view to custom it:

```bash
php artisan vendor:publish --tag=wizard-views-bs4
```

Now you can customize `resources/views/vendor/wizard/*.blade.php` in your laravel project.

But if you want custom only one wizard view base view, you can copy the views from `resources/views/vendor/wizard/*.blade.php` to `resources/views/wizards/user/*.blade.php`. (`user` is `wizardName` property value on your wizard controller),

## Step

### Get cached data

For example, `FirstStep` has `name` and `email` fields, `SecondStep` has `age` and `phone` fields. you can use step's `data` method to get step data:

```php
$name = $firstStep->data('name');
// 'Lucas'

$data = $secondStep->data();
// ['age' => '30', 'phone' => '0900111222']
```

Or you can use Step repository to get other step data:

```php
$data = $secondStep->getRepo()->find('first')->data();
// ['name' => 'Lucas']

$name = $secondStep->getRepo()->find('first')->data('name');
// 'Lucas'
```

### Step repository

Step repository saves all steps data, if you want use other step, you need to use it:

From wizard:

```php
$stepRepo = $wizard->stepRepo();
```

From step:

```php
$stepRepo = $step->getRepo();
```

Get previous step:

```php
$prevStep = $step->prev();
// same as:
$prevStep = $step->getRepo()->prev();
```

Get next step:

```PHP
$prevStep = $step->next();
// same as:
$nextStep = $step->getRepo()->next();
```

Step repository all can use method detailed reference: https://github.com/ycs77/laravel-wizard/blob/v2.x/src/StepRepository.php

### Upload Files

Since **v2.3.3** upload files in **Cache** and **No Cache** are supported, if use the **Cache Mode** you can cache all input data and uploaded files to save in the last step:

```php
<?php

class LastStep extends Step
{
    public function model(Request $request)
    {
        return $request->user();
    }

    public function saveData(Request $request, $data = null, $model = null)
    {
        $data = [
            'avatar' => $this->getRepo()->find('has-avatar-step')->data('avatar'),
        ];

        $data['avatar'] = $data['avatar']->store('avatar', ['disk' => 'public']);

        $model->update($data);
    }
}
```

Then add a step view to upload the avatar image:

*resources/views/steps/user/has-avatar.blade.php*
```blade
<div class="form-group mb-3">
    <label for="avatar">Avatar</label>
    <input type="file" name="avatar" id="avatar" class="form-control">
    <div class="form-control d-none {{ $errors->has('avatar') ? 'is-invalid' : '' }}"></div>
    @if ($errors->has('avatar'))
        <span class="invalid-feedback">{{ $errors->first('avatar') }}</span>
    @endif
</div>
```

### Skip step

> **Note**: **v2.3.3+**

To make Step skippable, set the `$skip` property to `true`, then this Step will be skip the validation and save data:

*app/Steps/User/NameStep.php*
```php
<?php

...

class NameStep extends Step
{
    /**
     * Is it possible to skip this step.
     *
     * @var boolean
     */
    protected $skip = true;
}
```

### Passing data to views

Because each step is injected into the view of the step, so just add the method to return the data in the step class. For example, pass the data of the select options to view:

*app/Steps/User/NameStep.php*
```php
<?php

class NameStep extends Step
{
    public function getOptions()
    {
        return [
            'Taylor',
            'Lucas',
        ];
    }
}

```

*resources/views/steps/user/name.blade.php*
```blade
<div class="form-group mb-3">
    <label for="name">Select name</label>
    <select id="name" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
        <option value="">Select...</option>
        @foreach ($step->getOptions() as $option)
            <option value="{{ $option }}" @if (old('name') ?? $step->data('name') === $option) @endif>{{ $option }}</option>
        @endforeach
    </select>

    @if ($errors->has('name'))
        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
    @endif
</div>

```

The `getOptions` method is custom, can be changed at will.

### Save data on other step

Suppose there are now two Steps `NameStep` and `EmailStep`.
First, don't set the Model for all Steps, but don't use the last one:

*app/Steps/User/NameStep.php*
```php
<?php

class NameStep extends Step
{
    public function model(Request $request)
    {
        //
    }

    public function saveData(Request $request, $data = null, $model = null)
    {
        //
    }
}
```

Next, receive all the data in the last Step and save Model:

*app/Steps/User/EmailStep.php*
```php
<?php

class EmailStep extends Step
{
    public function model(Request $request)
    {
        return new User();
    }

    public function saveData(Request $request, $data = null, $model = null)
    {
        $data = $this->getStepsData();
        $model->fill($data)->save();
    }
}
```

### Set relationships model

Similarly, you can set the relationships model in `model` method of the `Step`.

```php
use Illuminate\Support\Arr;

/**
 * Set the step model instance or the relationships instance.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null
 */
public function model(Request $request)
{
    return $request->user()->posts();
}

/**
 * Save this step form data.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  array|null  $data
 * @param  \Illuminate\Database\Eloquent\Model\Illuminate\Database\Eloquent\Relations\Relation|null  $model
 * @return void
 */
public function saveData(Request $request, $data = null, $model = null)
{
    $data = Arr::only($data, ['title', 'content']);
    $model->create($data);
}
```

## Commands

**Make wizard**:

The `make:wizard` command and `make:wizard:controller` command difference, is `make:wizard` command will append route and no confirm generate step.

```bash
php artisan make:wizard User NameStep,EmailStep
```

**Make controller**:

The `make:wizard:controller` command only generate the `WizardController`, `NameStep`, `EmailStep` class.

```bash
php artisan make:wizard:controller UserController --steps=NameStep,EmailStep
```

**Make step**:

```bash
php artisan make:wizard:step NameStep
```

With step label and wizard:

```bash
php artisan make:wizard:step NameStep --label="Name" --slug=name --wizard=user
```

Add custom view path:

```bash
php artisan make:wizard:step NameStep --label="Name" --slug=name --view=steps.user.name --wizard=user
```

## Credits

* [smajti1/laravel-wizard](https://github.com/smajti1/laravel-wizard)

## License

[MIT LICENSE](LICENSE)

[ico-version]: https://img.shields.io/packagist/v/ycs77/laravel-wizard?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square
[ico-style-ci]: https://github.styleci.io/repos/190876726/shield?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ycs77/laravel-wizard?style=flat-square

[link-packagist]: https://packagist.org/packages/ycs77/laravel-wizard
[link-style-ci]: https://github.styleci.io/repos/190876726
[link-downloads]: https://packagist.org/packages/ycs77/laravel-wizard
