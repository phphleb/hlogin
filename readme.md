## HLOGIN

-------------------------

[![HLEB2](https://img.shields.io/badge/HLEB-2-darkcyan)](https://github.com/phphleb/hleb) ![PHP](https://img.shields.io/badge/PHP-^8.2-blue) [![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/hleb/blob/master/LICENSE)

The HLOGIN library expands the capabilities of the [HLEB2](https://github.com/phphleb/hleb) framework by adding full-fledged user registration on the site which is characterized by simplicity of settings and quick installation and (at the same time) convenient and diverse functionality that supports multilingualism and several design options. Optionally you can display a feedback form that goes in addition to registration and authorization. In the automatically created admin panel you will find tools for user management and display settings. After that you can immediately direct your thoughts to creating content for the site.

Supported  __MySQL__ / __MariaDB__ / __PostgreSQL__ / _SQLite__

Required PHP extensions: __json__, __gd__,  __pdo__, __pdo-mysql__ / __pdo-pgsql__, __pdo-sqlite__, __readline__

[**Link to instructions**](https://hleb2framework.ru)

[**Demo site**](https://auth2.phphleb.ru/)


### Installation
Step 1. Installation via Composer into an existing HLEB2 project:
 ```bash
 $ composer require phphleb/hlogin
 ```

Step 2. Installing the library into the project. You will be asked to choose a design from several:

 ```bash
 $ php console phphleb/hlogin --add
 ```

 ```bash
 $ composer dump-autoload
 ```

### Connection
Step 3. Before doing this action you must have a valid connection to the database. In the project settings `/config/database.php` you need to specify the connection and specify its name there in the `base.db.type` option.
After that the console command creates the necessary tables and a user with administrator rights (you will be prompted to specify an E-mail and password):

 ```bash
 $ php console hlogin/create-login-table
 ```
 ```bash
 $ php console hlogin/create-admin
 ```

If it is not possible to execute a console command, then create tables using an SQL query from the `/vendor/phphleb/hlogin/planB.sql` file.
After that, register an administrator and set his _regtype_ equal to 11.

Step 4. Now you can go to the main stub page of the site if it is installed without changes and make sure that the authorization panels are available.
If the library is not installed in the developed project on the HLEB2 framework from the beginning you should check the entry on the `/en/login/action/enter/` page of the site.

Step 5. Setting up registration on the site on specific pages through routing.
To do this you need to set the following conditions in the routing files (project folder `/routes/`):

```php
use App\Middlewares\Hlogin\Registrar;
use Phphleb\Hlogin\App\RegType;

Route::toGroup()->middleware(Registrar::class, data: [RegType::UNDEFINED_USER, '>=']);
// Routes in this group will be available to all unregistered and registered users except those that were marked deleted and banned.
Route::endGroup();

Route::toGroup()->middleware(Registrar::class, data: [RegType::PRIMARY_USER, '>=']);
// Routes in this group will be available to those who pre-registered (but didn't confirm E-mail), as well as to all registered users (including administrators).
Route::endGroup();

Route::toGroup()->middleware(Registrar::class, data: [RegType::REGISTERED_USER, '>=']);
// Routes in this group will be available to all users who have completed full registration (confirmed by E-mail including administrators).
Route::endGroup();

Route::toGroup()->middleware(Registrar::class, data: [RegType::REGISTERED_COMMANDANT, '>=']);
// Routes in this group will be available only to administrators.
Route::endGroup();

Route::toGroup()->middleware(Registrar::class, data: [RegType::PRIMARY_USER, '>=', Registrar::NO_PANEL]);
// Routes with check registration without displaying standard panels and buttons.
Route::endGroup();

Route::toGroup()->middleware(Registrar::class, data: [RegType::PRIMARY_USER, '>=', Registrar::NO_BUTTON]);
// Routes with check registration without displaying standard buttons.
Route::endGroup();
```

It should be borne in mind that pages that don't fall into any of these groups with conditions are outside the registration rules and this library is not connected to them.

Step 6. Configuration. After authorization in the administrator profile (`/en/login/profile/`) the login button to the admin panel is displayed. You can configure registration panels and other conditions there.

If you need to display data depending on the type of user registration you can use the following checks:

```php
use Phphleb\Hlogin\App\AuthUser;

$user = AuthUser::current();
if ($user) {
    // Status for the confirmed user.
    $confirm = $user->isConfirm();

    // Obtaining the user's E-mail.
    $email = $user->getEmail();

    // Result of the administrator check.
    $isAdmin = $user->isSuperAdmin();
    // ... //
} else {
    // The current user is not authorized.
}
```
------

**More detailed information in the [framework documentation](https://hleb2framework.ru/)**.
