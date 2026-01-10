> ## ⚠️ THIS REPOSITORY IS DEPRECATED ⚠️
>
> **This package is no longer maintained by 24Slides.**
> It will not receive any further updates, bug fixes, or security patches. We strongly recommend using an alternative, actively maintained SAML package for Laravel.
> You are free to fork this repository and continue its development independently.

## [Laravel 5.4+] SAML Service Provider 

[![Latest Stable Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Total Downloads][ico-downloads]][link-downloads]

An integration to add SSO to your service via SAML2 protocol based on [OneLogin](https://github.com/onelogin/php-saml) toolkit. 

This package turns your application into Service Provider with the support of multiple Identity Providers.

## Requirements

- Laravel 5.4+
- PHP 7.0+

## Getting Started

### Installing

##### Step 1. Install dependency

```
composer require 24slides/laravel-saml2
```

If you are using Laravel 5.5 and higher, the service provider will be automatically registered.

For older versions, you have to add the service provider and alias to your `config/app.php`:

```php
'providers' => [
    ...
    Slides\Saml2\ServiceProvider::class,
]

'alias' => [
    ...
    'Saml2' => Slides\Saml2\Facades\Auth::class,
]
```

##### Step 2. Publish the configuration file.

```
php artisan vendor:publish --provider="Slides\Saml2\ServiceProvider"
```

##### Step 3. Run migrations

```
php artisan migrate
```

### Configuring

Once you publish `saml2.php` to `app/config`, you need to configure your SP. Most of options are inherited from [OneLogin Toolkit](https://github.com/onelogin/php-saml), so you can check documentation there.

#### Service Provider Certificate Creation (SP certificate)

```
php artisan saml2:cert
```

Once you create the certificates, configure your SP `SAML2_SP_CERT_x509` and `SAML2_SP_CERT_PRIVATEKEY`.

#### Identity Providers (IdPs)

To distinguish between identity providers there is an entity called Tenant that represent each IdP.

When request comes to an application, the middleware parses UUID and resolves the Tenant.

You can easily manage tenants using the following console commands:

- `artisan saml2:create-tenant`
- `artisan saml2:update-tenant`
- `artisan saml2:delete-tenant`
- `artisan saml2:restore-tenant`
- `artisan saml2:list-tenants`
- `artisan saml2:tenant-credentials`

> To learn their options, run a command with `-h` parameter.

Each Tenant has the following attributes:

- **UUID** — a unique identifier that allows to resolve a tenannt and configure SP correspondingly
- **Key** — a custom key to use for application needs
- **Entity ID** — [Identity Provider Entity ID](https://spaces.at.internet2.edu/display/InCFederation/Entity+IDs)
- **Login URL** — Identity Provider Single Sign On URL
- **Logout URL** — Identity Provider Logout URL
- **x509 certificate** — The certificate provided by Identity Provider in **base64** format
- **Metadata** — Custom parameters for your application needs

#### Default routes

The following routes are registered by default:

- `GET saml2/{uuid}/login`
- `GET saml2/{uuid}/logout`
- `GET saml2/{uuid}/metadata`
- `POST saml2/{uuid}/acs`
- `POST saml2/{uuid}/sls`

You may disable them by setting `saml2.useRoutes` to `false`.

> `/saml2` prefix can be changed via `saml2.routesPrefix` config parameter.

## Usage

### Authentication events

The simplest way to handle SAML authentication is to add listeners on `Slides\Saml2\SignedIn` and `Slides\Saml2\SignedOut` events.

```php
Event::listen(\Slides\Saml2\Events\SignedIn::class, function (\Slides\Saml2\Events\SignedIn $event) {
    $messageId = $event->getAuth()->getLastMessageId();
    
    // your own code preventing reuse of a $messageId to stop replay attacks
    $samlUser = $event->getSaml2User();
    
    $userData = [
        'id' => $samlUser->getUserId(),
        'attributes' => $samlUser->getAttributes(),
        'assertion' => $samlUser->getRawSamlAssertion()
    ];
    
    $user = // find user by ID or attribute
    
    // Login a user.
    Auth::login($user);
});
```

### Middleware

To define a middleware for default routes, add its name to `config/saml2.php`:

```php
/*
|--------------------------------------------------------------------------
| Built-in routes prefix
|--------------------------------------------------------------------------
|
| Here you may define the prefix for built-in routes.
|
*/

'routesMiddleware' => ['saml'],
```

Then you need to define necessary middlewares for your group in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        ...
    ],
    'api' => [
        ...
    ],
    'saml' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
    ],
```

### Logging out

There are two ways the user can logout:
- By logging out in your app. In this case you SHOULD notify the IdP first so it'll close the global session.
- By logging out of the global SSO Session. In this case the IdP will notify you on `/saml2/{uuid}/slo` endpoint (already provided).

For the first case, call `Saml2Auth::logout();` or redirect the user to the route `saml.logout` which does just that. 
Do not close the session immediately as you need to receive a response confirmation from the IdP (redirection). 
That response will be handled by the library at `/saml2/sls` and will fire an event for you to complete the operation.

For the second case you will only receive the event. Both cases receive the same event. 

Note that for the second case, you may have to manually save your session to make the logout stick (as the session is saved by middleware, but the OneLogin library will redirect back to your IdP before that happens):

```php
Event::listen('Slides\Saml2\Events\SignedOut', function (SignedOut $event) {
    Auth::logout();
    Session::save();
});
```

### SSO-friendly links

Sometimes, you need to create links to your application with support of SSO lifecycle. It means you expect a user to be signed in once you click on that link.

The most popular example is generating links from emails, where you need to make sure when user goes to your application from email, he will be logged in.
To solve this issue, you can use helpers that allow you create SSO-friendly routes and URLs — `saml_url()` and `saml_route()`.

To generate a link, you need to call one of functions and pass UUID of the tenant as a second parameter, unless your session knows that user was resolved by SSO.

> To retrieve UUID based on user, you should implement logic that links your internal user to a tenant.

Then, it generates a link like this:
```
https://yourdomain/saml/63fffdd1-f416-4bed-b3db-967b6a56896b/login?returnTo=https://yourdomain.com/your/actual/link
```

Basically, when user clicks on a link, it initiates SSO login process and redirects it back to your needed URL. 

## Examples

### Azure AD

At this point, we assume you have an application on Azure AD that supports Single Sign On.

##### Step 1. Retrieve Identity Provider credentials

![Azure AD](https://i.imgur.com/xKLswxB.png)

You need to retrieve the following parameters:

- Login URL
- Azure AD Identifier
- Logout URL
- Certificate (Base64)

##### Step 2. Create a Tenant

Based on information you received below, create a Tenant, like this:

```
php artisan saml2:create-tenant \
  --key=azure_testing \
  --entityId=https://sts.windows.net/fb536a7a-7251-4895-a09a-abd8e614c70b/ \
  --loginUrl=https://login.microsoftonline.com/fb536a7a-7251-4895-a09a-abd8e614c70b/saml2 \
  --logoutUrl=https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0 \
  --x509cert="MIIC0jCCAbqgAw...CapVR4ncDVjvbq+/S" \
  --metadata="customer:11235,anotherfield:value" // you might add some customer parameters here to simplify logging in your customer afterwards
```

Once you successfully created the tenant, you will receive the following output:

```
The tenant #1 (63fffdd1-f416-4bed-b3db-967b6a56896b) was successfully created.

Credentials for the tenant
--------------------------

 Identifier (Entity ID): https://yourdomain.com/saml/63fffdd1-f416-4bed-b3db-967b6a56896b/metadata
 Reply URL (Assertion Consumer Service URL): https://yourdomain.com/saml/63fffdd1-f416-4bed-b3db-967b6a56896b/acs
 Sign on URL: https://yourdomain.com/saml/63fffdd1-f416-4bed-b3db-967b6a56896b/login
 Logout URL: https://yourdomain.com/saml/63fffdd1-f416-4bed-b3db-967b6a56896b/logout
 Relay State: / (optional)
```

##### Step 3. Configure Identity Provider

Using the output below, assign parameters to your IdP on application Single-Sign-On settings page.

![Azure AD](https://i.imgur.com/3hkjFLZ.png)

##### Step 4. Make sure your application accessible by Azure AD

Test your application directly from Azure AD and make sure it's accessible worldwide. 

###### Running locally

If you want to test it locally, you may use [ngrok](https://ngrok.com/).

In case if you have a problem with URL creation in your application, you can overwrite host header in your nginx host 
config file by adding the following parameters:

```
fastcgi_param HTTP_HOST your.ngrok.io;
fastcgi_param HTTPS on;
```

> Replace `your.ngrok.io` with your actual ngrok URL 

## Tests

Run the following in the package folder:

```
vendor/bin/phpunit
```

## Security

As this project is **no longer maintained**, security vulnerabilities will not be fixed by 24Slides. The email address previously listed for reporting is no longer monitored for this project.

## Credits

- [aacotroneo][link-original-author]
- [brezzhnev][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://poser.pugx.org/24slides/laravel-saml2/v/stable?format=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/24Slides/laravel-saml2.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/24slides/laravel-saml2.svg?style=flat-square
[ico-code-coverage]: https://img.shields.io/scrutinizer/coverage/g/24slides/laravel-saml2.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/24slides/laravel-saml2.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/24slides/laravel-saml2
[link-travis]: https://travis-ci.org/24Slides/laravel-saml2
[link-scrutinizer]: https://scrutinizer-ci.com/g/24slides/laravel-saml2/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/24slides/laravel-saml2
[link-code-coverage]: https://scrutinizer-ci.com/g/24Slides/laravel-saml2
[link-downloads]: https://packagist.org/packages/24slides/laravel-saml2
[link-original-author]: https://github.com/aacotroneo
[link-author]: https://github.com/brezzhnev
[link-contributors]: ../../contributors
