 ## HLOGIN
 
The HLOGIN library expands the capabilities of the [HLEB](https://github.com/phphleb/hleb) framework by adding full-fledged user registration on the site which is characterized by simplicity of settings and quick installation and (at the same time) convenient and diverse functionality that supports multilingualism and several design options. Optionally you can display a feedback form that goes in addition to registration and authorization. In the automatically created admin panel you will find tools for user management and display settings. After that you can immediately direct your thoughts to creating content for the site.

Supported  __MySQL__ / __MariaDB__ / __PostgreSQL__

Required PHP extensions: __json__, __gd__, __pdo__, __pdo-mysql__ / __pdo_pgsql__, __readline__

[Demo page](https://auth.phphleb.ru/)

[Link to instructions](https://phphleb.ru/ru/v1/authorization/) (RU)


### Installation
Step 1. Installation via Composer into an existing HLEB project:
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
Step 3. Before doing this action you must have a valid connection to the database. In the project settings `/database/dbase.config` you need to specify the connection and specify its name there in the `HLEB_TYPE_DB` constant. After that the console command creates the necessary tables and a user with administrator rights (you will be prompted to specify an E-mail and password):

 ```bash
 $ php console hlogin/create-login-table-task
 ```

If it is not possible to execute a console command, then create tables using an SQL query from the `/vendor/phphleb/hlogin/standard_users.sql` file. After that, register an administrator and set his `reqtype` equal to 11.

Step 4. Now you can go to the main stub page of the site if it is installed without changes and make sure that the authorization panels are available. If the library is not installed in the developed project on the HLEB framework from the beginning you should check the entry on the `/en/login/action/enter/` page of the site.

Step 5. Setting up registration on the site on specific pages through routing. To do this you need to set the following conditions in the routing files (project folder `/routes/`):

 ```php
use Phphleb\Hlogin\App\System\UserRegistration as RegType;
use Phphleb\Hlogin\App\OriginData as RegData;

Route::before('Hlogin\Registrar', [RegType::UNDEFINED_USER, '>='])->getGroup();
// Routes in this group will be available to all unregistered and registered users except those that were marked deleted and banned.
Route::endGroup();

Route::before('Hlogin\Registrar', [RegType::PRIMARY_USER, '>='])->getGroup();
// Routes in this group will be available to those who pre-registered (but didn't confirm E-mail), as well as to all registered users (including administrators).
Route::endGroup();

Route::before('Hlogin\Registrar', [RegType::REGISTERED_USER, '>='])->getGroup();
// Routes in this group will be available to all users who have completed full registration (confirmed by E-mail including administrators).
Route::endGroup();

Route::before('Hlogin\Registrar', [RegType::REGISTERED_COMANDANTE, '='])->getGroup();
// Routes in this group will be available only to administrators. 
Route::endGroup();

Route::before('Hlogin\Registrar', [RegType::PRIMARY_USER, '>=', RegData::HIDE_PANELS])->getGroup();
// Routes with check registration without displaying standard panels and buttons.
Route::endGroup();

Route::before('Hlogin\Registrar', [RegType::PRIMARY_USER, '>=', RegData::HIDE_BUTTONS])->getGroup();
// Routes with check registration without displaying standard buttons.
Route::endGroup();
````

It should be borne in mind that pages that don't fall into any of these groups with conditions are outside the registration rules and this library is not connected to them.

Step 6. Configuration. After authorization in the administrator profile (`/en/login/profile/`) the login button to the admin panel is displayed. You can configure registration panels and other conditions there.

### Extra

If you need to display data depending on the type of user registration you can use the following checks:
 ```php
<?php
use Phphleb\Hlogin\App\System\UserRegistration;
   $userId = UserRegistration::getUserId(); // Returns the current user ID or NULL if not registered.

   if (UserRegistration::checkDeleted()) { /*...*/ } // Checking for a deleted or blocked current user.
   if (UserRegistration::checkActiveUser()) { /*...*/ } //  Check for any unblocked user.
   if (UserRegistration::checkPrimaryAndHigher()) { /*...*/ } //  Check for any registration without a confirmed E-mail and above.
   if (UserRegistration::checkRegisterAndHigher()) { /*...*/ } //  Check for registered and administrator.
   
   if (UserRegistration::getNumericType() === UserRegistration::REGISTERED_COMANDANTE){ /*...*/ } // Check for administrator.

 ```


Force setting the design of panels on the page:
 ```php
<?php
   Phphleb\Hlogin\App\OriginData::setLocalDesign('dark');

 ```

Force setting the language of the panels on the page (by default the language is obtained from the url parameter (following the domain) or the lang tag in the <html lang='en'>tag):
 ```php
<?php
  Phphleb\Hlogin\App\OriginData::setLocalLang('en');

 ```

You can replace the standard authorization buttons with any ones having previously disabled the standard ones in the admin panel. Own buttons can be assigned one of the following actions:
 ```javascript
<script>
// Setting the design for the page via JS. For example this can be set to the type `special` for visually impaired users.
hloginSetDesignToPopups('dark');

//  Returns the design type to its original state.
hloginRevertDesignToPopups();

// Close all registration windows.
hloginCloseAllPopups();

// Opens a specific window, in this case a user registration.
hloginVariableOpenPopup('UserRegister');
// Or 'UserEnter', 'UserProfile', 'ContactMessage'

// Outputs an arbitrary custom message in the current design.
hloginOpenMessage('Title', 'Message text', 'OK');

// Calling popups with custom blocks 'input' and 'button'.
hloginOpenMessage('Title', hloginPopupInput('Main field', 'Field-ID') + hloginPopupButton('Button name', 'alert(1)'));


// If this function exists it will be called every time you open a window passing the window type.
function _hloginMainVariableFunction(popupType) {
  // popupType = 'UserRegister' / 'UserEnter' / 'UserPassword' / 'ContactMessage'
}
</script>
 ```

Example:
 ```php
<?php
use Phphleb\Hlogin\App\System\UserRegistration;
if (UserRegistration::checkPrimaryAndHigher()) {
   print '<button onclick="hloginVariableOpenPopup(\'UserProfile\')">Open user profile panel</button>';
} else {
    print '<button onclick="hloginVariableOpenPopup(\'UserRegister\')">Open registration panel</button>';
    print '<button onclick="hloginVariableOpenPopup(\'UserEnter\')">Open login panel</button>';
}
 print '<button onclick="hloginVariableOpenPopup(\'ContactMessage\')">Open the message sending panel</button>';
 print '<button onclick="hloginSetDesignToPopups(\'special\')">Version for the visually impaired</button>';  
 print '<button onclick="hloginRevertDesignToPopups()">Undo version change</button>'; 
````

As you can understand registration cannot be accessed by users with disabled javascript in the browser. Now there are almost no such people.

If you need to direct the user straight to the login or registration page then several such pages are created:

Registration page
```html
/en/login/action/registration/
```

Login page
```html
/en/login/action/enter/
```

Profile page
```html
/en/login/profile/
```

Feedback page
```html
/en/login/action/contact/
```

Auto Logout Page
```html
/en/login/action/exit/
```

Admin panel page with registration settings
```html
/en/adminzone/registration/settings/
```

Admin panel page with a list of users
```html
/en/adminzone/registration/users/
```

Admin panel page for managing user data
```html
/en/adminzone/registration/rights/
```



When validating values ​​on the backend side (sent from registration forms) you can also handle it with your own PHP handler, if it is available. This way you can, for example, add your own field to the form and check it by yourself.
 ```php
<?php
// File /app/Optional/MainHloginExplorer.php
namespace App\Optional;
class MainHloginExplorer {
    /** @return bool */
    public function check() {
       return true; // Checking your own validation conditions.
    }
    /**
     * @param string|false $value - promo code value.
     * @param int $userId - - user identifier (received after registration) for which the value came.
     */
    public function setPromocode($value, $userId) {
      // Processing a promo code if this option is selected in the admin area
    }
}
 ```

### Design
Your own design is available when you select the blank type in the admin panel. After that, you can copy it to the `/public/css/` folder and change the CSS file of any other design from the existing ones by connecting it to the site by yourself.
```css
.hlogin-wn[data-type='base'] input {
   /* CSS rule for "input" design "base" */
}
.hlogin-wn[data-type='dark'] input {
   /* CSS rule for "input" design "dark" */
}
```

### Localization

To include files with your own translations, set two constants in the framework config. These files will replace the default ones.

```php
/*
|-----------------------------------------------------------------------------
| Sets a custom path for a folder with frontend localization.
|
| Устанавливает собственный путь для папки с фронтенд локализацией.
|
*/
define('HLOGIN_LOCALIZE_FRONTEND_DIR', '/vendor/phphleb/hlogin/resource/all/js/');

/*
|-----------------------------------------------------------------------------
| Sets a custom path for a folder with backend localization.
|
| Устанавливает собственный путь для папки с бэкенд-локализацией.
|
*/
define('HLOGIN_LOCALIZE_BACKEND_DIR', '/vendor/phphleb/hlogin/App/Langs/');
```

You can add your own localization by naming the files accordingly.

### Admin area

When creating your own sub pages in the admin panel surround their routes with access restrictions as shown below:

```php
use Phphleb\Hlogin\App\System\UserRegistration as RegType;
  Route::before('Hlogin\Registrar', [RegType::REGISTERED_COMANDANTE, '='])->getGroup();
  // Routes in this group will be available only to administrators.  
  Route::endGroup();

````

The creation of pages in the admin section is described in the library [phphleb/adminpan](https://github.com/phphleb/adminpan).

### Mailing of letters
Sending letters with notifications and restoration of access is carried out using the [phphleb/muller](https://github.com/phphleb/muller) library.

In the admin panel the sender's E-mail is indicated for which sending from the server should be allowed; for most hosting services it is enough to create such a mailbox.
The sending email can be found in `php.ini` (sendmail_path = ... -f'email@example.com').

By default messages are additionally logged to the `/storage/logs/` folder with `mail.log` ending.


### Individual mail server

To replace the standard sending, you need to create the _App\Optional\HloginMailServer_ class based on _DefaultMail_.
```php
<?php
// File /app/Optional/HloginMailServer.php
namespace App\Optional;
use Phphleb\Muller\Src\DefaultMail;
class HloginMailServer extends DefaultMail 
{ 
   /** @inheritDoc */
   public function send() { /* ... */ return true; }  

   /** @inheritDoc */
   public function savePostInFile() { /* ... */ }
     
   public function setParameters($parameters = '') { }
}
```


### Update

When upgrading the library version it is highly recommended to repeat the path from the installation, reassign the design and update the autoloader. You don't need to create tables and an administrator during the upgrade.

 ```bash
 $ composer update phphleb/hlogin

 $ php console phphleb/hlogin --add

 $ composer dump-autoload

 $ php console --clear-routes-cache 
 ```

-----------------------------------

[![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/draft/blob/main/LICENSE) ![PHP](https://img.shields.io/badge/PHP-^7.4.0-blue) ![PHP](https://img.shields.io/badge/PHP-8-blue) ![PHP](https://img.shields.io/badge/HLEB%20Framework->=1.5.82-brightgreen)


