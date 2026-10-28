# GemaDigital Laravel Framework

<a href="https://packagist.org/packages/gemadigital/framework" title="Latest Version on Packagist"><img src="https://img.shields.io/packagist/v/gemadigital/framework.svg?style=flat-square"></a>
<a href="https://packagist.org/packages/gemadigital/framework" title="Total Downloads"><img src="https://img.shields.io/packagist/dt/gemadigital/framework.svg?style=flat-square"></a>
<a href="https://github.com/the-whole-fruit/manifesto"><img src="https://img.shields.io/badge/writing%20standard-the%20whole%20fruit-brightgreen" title="We believe writing good code is not only about writing good code. It’s also about the words around it. We aims to deliver both: code and words."></a>

GemaDigital Laravel Framework

## Comands

Install the project
``` bash
$ php artisan install
```

Run the project (using env information)
``` bash
$ php artisan run
```

Packages a ready for production zip
``` bash
$ php artisan package
```

Publishes the framework assets
``` bash
$ php artisan publish
```

## Mail through Microsoft Graph

Sends mail with the Microsoft Graph API instead of SMTP. The framework adds the `microsoft-graph` mailer to the app's mail config, so `config/mail.php` doesn't change. Set in `.env`
```
MAIL_MAILER=microsoft-graph
MICROSOFT_GRAPH_TENANT_ID=
MICROSOFT_GRAPH_CLIENT_ID=
MICROSOFT_GRAPH_CLIENT_SECRET=
# Optional: mailbox that sends, defaults to MAIL_FROM_ADDRESS
MICROSOFT_GRAPH_SENDER=
```

When the `MICROSOFT_GRAPH_*` keys are empty, the `AZURE_*` ones of the Azure login are used. A `microsoft-graph` mailer in the app's `config/mail.php` overrides these values. The Entra ID app needs the `Mail.Send` application permission, restricted to the sending mailbox. Each message goes in a single request, so attachments are limited to about 3 MB.

## Login by email code

Logs in to the admin panel without passwords. Set in `.env`
```
MAGIC_LINK_LOGIN=true
```

The login form asks only for the email and sends it a 6-digit code, typed in 6 boxes in the same browser; pasting it, or the phone filling it in, works too. The email also has a button to log in with one click: in the browser that asked for the code it logs in right away, in any other only after a click on its page, so mail scanners that open links don't use it up. The code and the button last 15 minutes and work once; using one drops the other, and asking again drops both. A code stops working after 5 wrong tries. Backpack's password login, registration form and password reset go away; the Microsoft and Google buttons stay. My Account loses its password fields: the name can still change, but the email is the login and becomes read-only, since Backpack only changes it with the current password. An admin changes it in the users list, which has no password fields either: new users get a random password, as on a first login. The framework's views replace the theme's `auth/login/inc/form` and `my_account`, the app's own copies included.

The first login with a new email asks for the name and creates the account without admin permission, and only while registration is open (`REGISTRATION_OPEN` or `BACKPACK_REGISTRATION_OPEN`). Codes and links are kept, hashed, in the app's cache, with at most 3 codes per email and 20 per IP every 10 minutes. The button's link uses the request's host only when it is `APP_URL`'s domain or a subdomain of it, otherwise `APP_URL`. The framework also translates to Portuguese the greeting, sign-off and footer of Laravel's emails, unless the app's `lang/pt.json` has them.

The login follows the routes in use: after changing `MAGIC_LINK_LOGIN`, rebuild or clear the route cache (`php artisan route:clear`), as until then the old login stays whole.

When mail is down, this prints a link
```
$ php artisan auth:magic-link email@example.com
```

## Installation

Via Composer

``` bash
$ composer require gemadigital/framework
```

## Credits

- [António Almeida][link-author]

## License

Please see the [license file](license.md) for more information.

[link-author]: https://github.com/promatik
