# NovaSettingsTool

[![Latest Version on Packagist](https://img.shields.io/packagist/v/Pkaratanev/nova-settings-tool.svg?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)
[![Latest Stable Version](https://poser.pugx.org/Pkaratanev/nova-settings-tool/v/stable?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)
[![Latest Unstable Version](https://poser.pugx.org/Pkaratanev/nova-settings-tool/v/unstable?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)
[![Total Downloads](https://img.shields.io/packagist/dt/Pkaratanev/nova-settings-tool.svg?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)
[![License](https://poser.pugx.org/Pkaratanev/nova-settings-tool/license?style=flat-square?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)
[![Monthly Downloads](https://poser.pugx.org/Pkaratanev/nova-settings-tool/d/monthly?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)
[![Daily Downloads](https://poser.pugx.org/Pkaratanev/nova-settings-tool/d/daily?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)
[![composer.lock](https://poser.pugx.org/Pkaratanev/nova-settings-tool/composerlock?style=flat-square)](https://packagist.org/packages/Pkaratanev/nova-settings-tool)

A Laravel Nova Tool to let users manage global settings created from code. This package works only in combination with Laravel Nova.

![NovaSettingsTool](./screenshots/screenshot_2.png?raw=true "NovaSettingsTool")

## Table of Contents
* **[Installation](#installation)**
  * [Install Package](#install-package)
  * [Run Migrate](#run-migrate)
  * [Publish Config & Translations](#publish-config--translations)
* **[Usage](#usage)**
  * [Register Tool](#register-tool)
  * [Create Listener](#create-listener)
  * [Register Listener](#register-listener)
  * [Define Setting](#define-settings)
* **[Object Methods](#object-methods)**
  * [`SettingRegister`](#settingregister)
  * [`SettingGroup`](#settinggroup)
  * [`SettingItem`](#settingitem)
* **[Helper Methods](#helper-methods)**
* **[`SettingType` Representation](#settingtype-representation)**
* **[Config Options](#config-options)**
* **[Translation Options](#translation-options)**
* **[`SettingItem` Options](#settingitem-options)**
* **[Tested Fields](#tested-fields)**
* **[Screenshots](#screenshots)**
* **[License](#license)**


## Installation
Install `NovaSettingsTool` by following the steps below.


### Install Package
Install this package through composer using the following command:

```bash
composer require Pkaratanev/nova-settings-tool
```


### Run Migrate
This package uses a database table to store the setting values. Run the migrate command to create the table in the databasem, using the following command:

```bash
php artisan migrate
```

### Publish Config & Translations
This packages comes with a config file with options and translations. In order to change them, publish these config and translation files using the following command:

```bash
php artisan vendor:publish --tag="settings"
```


## Usage

### Register Tool
Register the tool with Nova. Add the tool to the `tools()` method of the `App\NovaServiceProvider`, as shown below:
```php
use Pkaratanev\NovaSettingsTool\NovaSettingsTool;

// ...

public function tools()
{
    return [
        // ...
        new NovaSettingsTool(),
        // ...
    ];
}

```


### Create Listener
Create a listener class where the settings tool will request for groups and items. A listener class can be placed in for example *App/Handlers* and can be named for example _SettingsRegisteringListener.php_. The `handle()` method is used to define the setting groups and setting items, it can be used as shown below:
```php
use Pkaratanev\NovaSettingsTool\Enums\SettingType;
use Pkaratanev\NovaSettingsTool\Events\SettingsRegistering;
use Pkaratanev\NovaSettingsTool\ValueObjects\SettingGroup;
use Pkaratanev\NovaSettingsTool\ValueObjects\SettingItem;

// ...

class SettingsRegisteringListener
{
    // ...
    public function handle(SettingsRegistering $event)
    {
        // ...
    }
    // ...
}

```


### Register Listener
Register the listener with Nova. Add the `SettingsRegistering` event containing the listener class(es) to the `$listen` array of the `App\EventServiceProvider`, as shown below:
```php
use Pkaratanev\NovaSettingsTool\Events\SettingsRegistering;
use App\Handlers\SettingsRegisteringListener;

// ...

protected $listen = [
    // ...
    SettingsRegistering::class => [
        SettingsRegisteringListener::class
    ]
    // ...
];

```

### Create Updated Values Listener
Create a listener class where you can watch for updated values and do something if they changed
```php
use Pkaratanev\NovaSettingsTool\Events\SettingsUpdated;

// ...

class SettingsRegisteringListener
{
    // ...
    public function handle(SettingsUpdated $event)
    {
        // ...
    }
    // ...
}

```


### Register Updated Listener
```php
use Pkaratanev\NovaSettingsTool\Events\SettingsUpdated;
use App\Handlers\SettingsUpdatedListener;

// ...

protected $listen = [
    // ...
    SettingsUpdated::class => [
        SettingsUpdatedListener::class
    ]
    // ...
];

```



### Define Settings
Define the settings in the `handle()` method in the listener, or in that method in another listener that listens to the `SettingsRegistering` event. Shown below is an example of how to define settings:
```php
public function handle(SettingsRegistering $event)
{
    $event->settingRegister
        ->group('general', function(SettingGroup $group) {
            $group->name('General')
                ->icon('fa fa-cog')
                ->item('title', function (SettingItem $item) {
                    $item->name('Website Title');
                })
                ->item('description', function (SettingItem $item) {
                    $item->type(SettingType::TEXTAREA);
                });
        })
        ->group('privacy', function(SettingGroup $group) {
            $group
                ->name('Privacy')
                ->icon('fa fa-user-secret')
                ->item('log', function (SettingItem $item) {
                    $item->name('Log User Activity')
                        ->field(Boolean::make('log')->help(
                            'When enabled, user activity will be logged.'
                        ));
                });
        });
}
```


## Object Methods

### `SettingRegister`

#### `group(string $key, Closure $callback = null)`
Create a new `SettingGroup` or call an existing `SettingGroup` (with the same key) and add it to the `SettingRegister`.
##### Param `string $key`
<sub>The self-defined (or existing where to append to) key of the `SettingGroup` (normally snake cased).</sub>
##### Param `Closure $callback`
<sub>The method where the `SettingGroup` can be configured and where items can be defined. The created `SettingGroup` will be passed as parameter. The default value is null.</sub>
##### Returns `SettingRegister`
<sub>The current `SettingRegister` object (`$this`).</sub>

#### `getGroups()`
Obtain a `Collection` of all registered `SettingGroup` objects from the `SettingRegister` object.
##### Returns `\Illuminate\Support\Collection`
<sub>The collection of `SettingGroup` objects registered with the `SettingRegister`.</sub>

#### `getGroup(string $key)`
Obtain a `SettingGroup` from the `SettingRegister` by it's key. When the `SettingGroup` is not found, it will be created automatically.
##### Param `string $key`
<sub>The key of the `SettingGroup`.</sub>
##### Returns `SettingGroup`
<sub>The requested or newly created `SettingGroup`.</sub>



### `SettingGroup`

#### `key(string $key)`
Set the key of the `SettingGroup`. This is optional because the group gets it's key from the `group()` method in the `SettingRegister` class.
##### Param `string $key`
<sub>The self-defined key of the `SettingGroup` (normally snake cased).</sub>
##### Returns `SettingGroup`
<sub>The current `SettingGroup` object (`$this`).</sub>

#### `name(string $name)`
Set the name of the `SettingGroup`.
##### Param `string $name`
<sub>The displayed tab header of the `SettingGroup`.</sub>
##### Returns `SettingGroup`
<sub>The current `SettingGroup` object (`$this`).</sub>

#### `icon(string $icon)`
Set the icon of the `SettingGroup`. Icons are only shown when `show_icons` is set to `true` in the config file.
##### Param `string $icon`
<sub>The FontAwesome 5 icon class (for example: `fa fa-cog`) of the icon that needs to be displayed in the `SettingGroup` tab header.</sub>
##### Returns `SettingGroup`
<sub>The current `SettingGroup` object (`$this`).</sub>

#### `priority(int $priority)`
Set the priority of the `SettingGroup`. When not set, the `SettingGroup` tabs will be sorted based on the creation order.
##### Param `int $priority`
<sub>The priority of the `SettingGroup`.</sub>
##### Returns `SettingGroup`
<sub>The current `SettingGroup` object (`$this`).</sub>

#### `item(string $key, Closure $callback = null)`
Create a new SettingItem and add it to the `SettingGroup`.
##### Param `string $key`
<sub>The self-defined key of the `SettingItem` (normally snake cased).</sub>
##### Param `Closure $callback`
<sub>The method where the `SettingItem` can be configured. The created `SettingItem` will be passed as parameter. The default value is null.</sub>

#### `getItems()`
Obtain a `Collection` of all registered `SettingItem` objects from the `SettingGroup` object.
##### Returns `\Illuminate\Support\Collection`
<sub>The collection of `SettingItem` objects registered with the `SettingGroup`.</sub>

#### `getItem(string $key)`
Obtain a `SettingItem` from the `SettingGroup` by it's key. When the `SettingItem` is not found, it will be created automatically.
##### Param `string $key`
<sub>The key of the `SettingItem`.</sub>
##### Returns `SettingGroup`
<sub>The requested or newly created `SettingItem`.</sub>

#### `getKey()`
Get the key from the `SettingGroup`.
##### Returns `string`
<sub>The key of the `SettingGroup`.</sub>

#### `getName()`
Get the name from the `SettingGroup`.
##### Returns `string`
<sub>The key of the `SettingGroup`.</sub>

#### `getIcon()`
Get the icon from the `SettingGroup`.
##### Returns `string`
<sub>The key of the `SettingGroup`.</sub>

#### `getPriority()`
Get the priority from the `SettingGroup`.
##### Returns `int`
<sub>The key of the `SettingGroup`.</sub>

#### `hasItems()`
Check if the `SettingGroup` contains one or more `SettingItem` objects.
##### Returns `bool`
<sub>Indicated if the `SettingGroup` has any `SettingItem` objects.</sub>



### `SettingItem`

#### `key(string $key)`
Set the key of the `SettingItem`. This is optional because the item gets it's key from the `item()` method in the `SettingGroup` class.
##### Param `string $key`
<sub>The self-defined key of the `SettingItem` (normally snake cased).</sub>
##### Returns `SettingItem`
<sub>The current `SettingItem` object (`$this`).</sub>

#### `name(string $name)`
Set the name of the `SettingItem`.
##### Param `string $name`
<sub>The displayed title of the `SettingItem`.</sub>
##### Returns `SettingItem`
<sub>The current `SettingItem` object (`$this`).</sub>

#### `priority(int $priority)`
Set the priority of the `SettingItem`. When not set, the `SettingItem` rows will be sorted based on the creation order.
##### Param `int $priority`
<sub>The priority of the `SettingItem`.</sub>
##### Returns `SettingItem`
<sub>The current `SettingItem` object (`$this`).</sub>

#### `options(array $options)`
Set additional option to the `SettingItem`. The options array needs to be a `Key => Value` array. See the **[`SettingItem` Options](#settingitem-options)** for supported options.
##### Param `string $options`
<sub>The `Key => Value` array containing the preferred options.</sub>
##### Returns `SettingItem`
<sub>The current `SettingItem` object (`$this`).</sub>

#### `type(string $type)`
Set the `SettingType` of the `SettingItem`. Only values from the `SettingType` enumeration are supported. This is optional when a `Field` is set using the `field()` method.
##### Param `string $type`
<sub>The value of a `SettingType` constant.</sub>
##### Returns `SettingItem`
<sub>The current `SettingItem` object (`$this`).</sub>

#### `field(\Laravel\Nova\Fields\Field $field)`
Make a new `Field` instance and add it to the `SettingItem`. Check which `Field` classes proved to be working in the **[Tested Fields](#tested-fields)** section.
##### Param `\Laravel\Nova\Fields\Field $field`
<sub>The `Field` for the current `SettingItem`.</sub>

#### `value($value)`
Set a value programmatically. Normally this should be handled by the Tool based on user input.
##### Param `$value`
<sub>The value for a `SettingItem`.</sub>
##### Returns `SettingItem`
<sub>The current `SettingItem` object (`$this`).</sub>

#### `getKey()`
Get the key from the `SettingItem`.
##### Returns `string`
<sub>The key of the `SettingItem`.</sub>

#### `getName()`
Get the name from the `SettingItem`.
##### Returns `string`
<sub>The key of the `SettingItem`.</sub>

#### `getPriority()`
Get the priority from the `SettingItem`.
##### Returns `int`
<sub>The key of the `SettingItem`.</sub>

#### `getOptions()`
Get the options from the `SettingItem`.
##### Returns `array`
<sub>The options of the `SettingItem`.</sub>

#### `getType()`
Get the type from the `SettingItem`.
##### Returns `string`
<sub>The type of the `SettingItem`.</sub>

#### `getField()`
Get the `Field` from the `SettingItem`.
##### Returns `\Laravel\Nova\Fields\Field`
<sub>The `Field` of the `SettingItem`.</sub>

#### `getValue()`
Get the value from the `SettingItem`. It checks if the value is already obtained from the database, else it checks the database and when there is no value, it will return the default value (set through the `options()` method). When then there is still no value, it will return `null`.
##### Returns `mixed`
<sub>The value of the `SettingItem`.</sub>



## Helper Methods

#### `settings()`
Get the `SettingRegister` instance from the App Container. This method returns null if the `SettingRegister` is not initialized by the ServiceProvider.
##### Returns `SettingRegister|null`
<sub>The `SettingRegister` instance from the App Container or `null` when not initialized.</sub>

#### `setting(string $key)`
Get a `SettingItem` instance from the the SettingRegister in the App Container using the key of the `SettingItem`. This method returns null if the `SettingRegister` is not initialized by the ServiceProvider or the `SettingItem` does not exist.
##### Param `string $key`
<sub>The key of the `SettingItem`.</sub>
##### Returns `mixed`
<sub>The `SettingItem` instance request for or null if not found.</sub>

#### `settingValue(string $key)`
Get the value of a `SettingItem` instance from the the SettingRegister in the App Container using the key of the `SettingItem`. This method returns null if the SettingRegister is not initialized by the ServiceProvider, the `SettingItem` does not exist or if there is no value set.
##### Param `string $key`
<sub>The key of the `SettingItem`.</sub>
##### Returns `mixed`
<sub>The value of the `SettingItem` instance request for or null if not found.</sub>


## `SettingType` Representation
| `SettingType` constant | Value | Represents Nova Field |
| --- | --- | --- |
| `TEXT`| _text_ | `\Laravel\Nova\Fields\Text` |
| `BOOLEAN` | _boolean_ | `\Laravel\Nova\Fields\Boolean` |
| `NUMBER` | _number_ | `\Laravel\Nova\Fields\Number` |
| `TEXTAREA` | _textarea_ | `\Laravel\Nova\Fields\Textarea` |
| `DATE` | _date_ | `\Laravel\Nova\Fields\Date` |
| `DATETIME` | _datetime_ | `\Laravel\Nova\Fields\DateTime` |
| `CODE` | _code_ | `\Laravel\Nova\Fields\Number` |
| `PASSWORD` | _password_ | `\Laravel\Nova\Fields\Password` |


## Config Options
| Option | Information | Values |
| --- | --- | --- |
| `show_title` | Show the title of the module in the header of the settings tool page. | `true`  title is shown  `false`  title is hidden |
| `show_suffix`| The suffix of the tab title on the settings tool page (space included automatically). The suffix can be set in the translation (the default suffix is ` Settings`). | `true`  suffix is shown  `false`  suffix is hidden |
| `show_icons` | Determines if the prefix of the tab title on the settings tool page can hold an icon. The icon can be set when a `SettingGroup` is created. | `true`  icons are shown  `false`  icons are hidden |


## Translation Options
| Key | Information | Default English Value |
| --- | --- | --- |
| `settings_title`     | The title of the navigation item and the header of the tool page. | _Settings_ |
| `save_settings`      | The caption of the save button on the tool page. | _Save Settings_ |
| `setting_tab_suffix` | The suffix of the tabs (groups) on the tool page (first space is included automatically). | _Settings_ |
| `save_success`       | The toaster message when saving the settings succeeded. | _The settings are saved successfully!_ |
| `save_error`         | The toaster message when saving the settings fails. | _An error occurred while saving the settings!_ |
| `load_error`         | The toaster message when loading the settings fails. | _An error occurred while loading the settings!_ |
| `module_not_migrated`| The toaster message when the module is not migrated yet. | _The settings module is not migrated!_ |


## `SettingItem` options
| Key | Value Type | Information |
| --- | --- | --- |
| _default_ | `mixed` | Set a default value for the `SettingItem`. |


## Tested Fields
| Field Class | Passed Test | Not Supported (Yet) | Not Tested Yet |
| --- | --- | --- | --- |
| `\Laravel\Nova\Fields\Text` | x |  |
| `\Laravel\Nova\Fields\Boolean` | x |  |
| `\Laravel\Nova\Fields\Number` | x |  |
| `\Laravel\Nova\Fields\TextArea` | x |  |
| `\Laravel\Nova\Fields\Date` | x |  |
| `\Laravel\Nova\Fields\DateTime` | x |  |
| `\Laravel\Nova\Fields\Code` | x |  |
| `\Laravel\Nova\Fields\Password` | x |  |
| `\Laravel\Nova\Fields\Avatar` |  |  | x |
| `\Laravel\Nova\Fields\Country` |  |  | x |
| `\Laravel\Nova\Fields\Currency` |  |  | x |
| `\Laravel\Nova\Fields\File` |  |  | x |
| `\Laravel\Nova\Fields\Gravatar` |  |  | x |
| `\Laravel\Nova\Fields\ID` |  |  | x |
| `\Laravel\Nova\Fields\Image` |  |  | x |
| `\Laravel\Nova\Fields\Markdown` |  |  | x |
| `\Laravel\Nova\Fields\PasswordConfirmation` |  |  | x |
| `\Laravel\Nova\Fields\Place` |  |  | x |
| `\Laravel\Nova\Fields\Select` |  |  | x |
| `\Laravel\Nova\Fields\Status` |  |  | x |
| `\Laravel\Nova\Fields\Timezone` |  |  | x |
| `\Laravel\Nova\Fields\Trix` |  |  | x |


## Screenshots
![NovaSettingsTool](./screenshots/screenshot_1.png?raw=true "NovaSettingsTool")

![NovaSettingsTool](./screenshots/screenshot_2.png?raw=true "NovaSettingsTool")

![NovaSettingsTool](./screenshots/screenshot_3.png?raw=true "NovaSettingsTool")


## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
