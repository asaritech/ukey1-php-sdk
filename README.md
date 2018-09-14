# Ukey1 SDK for PHP

This repository contains the open source PHP SDK that allows you to access the **[Ukey1 API](https://ukey.one)** from your PHP app.

**!!! Please note that versions older than 3.0.0 are deprecated and don't work since November 15, 2017 !!!**

## About Ukey1

[Ukey1](https://ukey.one) is an Authentication and Data Protection Service with the mission to enhance security of websites. 
The service is designed to help you with EU GDPR compliance.

### Ukey1 flow for this PHP SDK

1. User clicks to "sign-in" button
  - you may use our [unified sign-in button](https://github.com/asaritech/ukey1-signin-button)
2. SDK sends a connection request to our API and gets a unique Gateway URL
3. User is redirected to Ukey1 Gateway
4. User signs in using their favourite solution and authorizes your app
5. User is redirected back to predefined URL
6. SDK checks the result and gets a unique access token
7. That's it - user is authenticated (your app can make API calls to get user's data)

### API specification

- [API specification](https://ukey1.docs.apiary.io/)
- [Documentation](https://asaritech.github.io/ukey1-docs/)

## Requirements

- PHP ^5.5|^7.0
- [guzzlehttp/guzzle ~6.0](http://guzzlephp.org)
- [lcobucci/jwt ^3.2](https://github.com/lcobucci/jwt)

## Installation

The Ukey1 PHP SDK can be installed with [Composer](https://getcomposer.org/) (recommended option). Run this command:

```bash
$ composer require asaritech/ukey1-php-sdk
```

## Usage

First, you need [credentials](https://dashboard.ukey.one/developer) (`App ID` and `Secret Key`). In our dashboard, we also recommend to activate as many protections as possible.

### Sign-in / sign-up / log-in - all buttons in one

Your app may look like this (of course, it's optional):

```html
<html>
  <head>
    <!-- ... -->
    <link rel="stylesheet" type="text/css" href="https://code.ukey1cdn.com/ukey1-signin-button/master/css/ukey1-button.min.css" media="screen">
  </head>
  <body>
    <!-- ... -->
    <a href="login.php" class="ukey1-button">Sign in via Ukey1</a>
    <!-- ... -->
  </body>
</html>
```

### Connection request

Your script `login.php` makes a request to our endpoint `/auth/v2/connect`.

```php
session_start();

use \Ukey1\App;
use \Ukey1\Endpoints\Authentication\Connect;
use \Ukey1\Endpoints\Authentication\SystemScopes;
use \Ukey1\Generators\RandomString;

// Set your domain name including protocol
//App::setDomain("http://example.org"); // if not provided, it will be set automatically

define("APP_ID", "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");
define("SECRET_KEY", "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");

// Don't forget to use try/catch, the SDK may throw exceptions

try {
  // Entity of your app
  $app = new App();
  $app->setAppId(APP_ID)
    ->setSecretKey(SECRET_KEY);

  // You need a request ID (no need to be unique but it's better)
  // It may be a random string or number
  // But it may also be your own reference ID
  // Maximum length is 64 bytes (=128 chars)
  $requestId = RandomString::generate(64); 

  // This is an URL for redirection back to the app
  // Do you know what is absolutely perfect?
  // - it may be unique
  // - it may contain query parameters and/or fragment
  $returnUrl = "http://example.org/login.php?action=check&user=XXX#fragment";

  // You can check what permissions you can ask (useful for development purposes)
  $systemModule = new SystemScopes($app);
  $permissions = $systemModule->getAvailablePermissions();
  //print_r($permissions);exit;

  // Endpoint module
  $connectModule = new Connect($app);
  $connectModule->setRequestId($requestId)
    ->setReturnUrl($returnUrl)
    ->setScope([
      "country",
      "language",
      "firstname",
      "surname",
      "email",
      "image"
    ]);
  $connectId = $connectModule->getId(); // $connectId is our reference ID (UUID, exactly 36 chars)

  // Store $requestId and $connectId in your database or session, you will need them later
  $_SESSION["requestId"] = $requestId;
  $_SESSION["connectId"] = $connectId;

  // Redirect user to Ukey1 Gateway
  $connectModule->redirect();

} catch (\Exception $e) {
  echo "Unfortunatelly, an error was occured: " . $e->getMessage();
  exit;
}
```

### Requests for access token and user details

Once the user authorizes your app, Ukey1 redirects the user back to your app to the URL you specified earlier. 
The same is done if user cancels the request.

URL will look like this: `http://example.org/login.php?action=check&user=XXX&_ukey1[request_id]={REQUEST_ID}&_ukey1[connect_id]={CONNECT_ID}&_ukey1[code]={CODE}&_ukey1[result]={RESULT}&_ukey1[signature]={SIGNATURE}#fragment` 
where `REQUEST_ID` is a previously used `$requestId`, `CONNECT_ID` is a previously used `$connectId`, `CODE` is a one-time code for getting the access token
`RESULT` may be *authorized* or *canceled* and `SIGNATURE` is a security signature.

```php
session_start();

use \Ukey1\App;
use \Ukey1\Endpoints\Authentication\AccessToken;
use \Ukey1\Endpoints\Authentication\User;

define("APP_ID", "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");
define("SECRET_KEY", "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");

// Don't forget to use try/catch, the SDK may throw exceptions

try {
  $app = new App();
  $app->setAppId(APP_ID)
      ->setSecretKey(SECRET_KEY);

  // Endpoint module
  // You needs $requestId and $connectId that you previously stored in your database or session
  // WARNING: DO NOT use values from GET query - the SDK will test if GET parameters are equal to those you provide here...
  $tokenModule = new AccessToken($app);
  $tokenModule->setRequestId($_SESSION["requestId"])
    ->setConnectId($_SESSION["connectId"]);
  $check = $tokenModule->check(); // returns true if user authorized the request

  if ($check) {
    $accessToken = $tokenModule->getAccessToken();

    // You can also get token expiration (in usual case it's only few minutes) and the list of granted permissions
    //$accessTokenExpiration = $tokenModule->getAccessTokenExpiration();
    //$grantedScope = $tokenModule->getScope();

    // You can now unset request ID and connect ID from session or your database
    unset($_SESSION["requestId"], $_SESSION["connectId"]);

    // Now you can read user's data
    $userModule = new User($app);
    $userModule->setAccessToken($accessToken);

    // If you don't need any personal data but ID, you can get user's ID without any other request (because it's stored in access token)
    $userId = $userModule->getId();

    // If you need more data, the following method will trigger request to get them
    $user = $module->getUser();

    $scope = $user->getScope();
    $firstname = $user->getFirstname();
    $surname = $user->getSurname();
    $language = $user->getLanguage();
    $country = $user->getCountry();
    $email = $user->getEmail();
    $image = $user->getImage();

    // For other permissions (if applicable) you can use general `get()` method
    $customScope = $user->get("another-available-scope");

    // ... more your code ...
  } else {
    // The request has been canceled by user...
  }

} catch (\Exception $e) {
  echo "Unfortunatelly, an error was occured: " . $e->getMessage();
  exit;
}
```

## Premium features

### Private users

This feature also known as *Extranet users* (must be enabled in Ukey1 dashboard) is useful when you want to implement Ukey1 into your private app where only predefined users can access (typically employees within company extranet).

The flow is similar. First, in your private app you need to have a simple user management. No password needed, only roles (if applicable), our User ID (that you will get at the end of the flow as usually) and Extranet Reference ID. This Reference ID serves for user deletion in the further future.

In your own user management, when you create a new user, you also have to make a POST request to our endpoint `/auth/v2/extranet/users`.

```php
session_start();

use \Ukey1\App;
use \Ukey1\Endpoints\Authentication\ExtranetUsers;

// Set your domain name including protocol
//App::setDomain("http://example.org"); // if not provided, it will be set automatically

define("APP_ID", "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");
define("SECRET_KEY", "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");

// Don't forget to use try/catch, the SDK may throw exceptions

try {
  // Entity of your app
  $app = new App();
  $app->setAppId(APP_ID)
    ->setSecretKey(SECRET_KEY);

  // Endpoint module
  $extranetModule = new ExtranetUsers($app);
  $extranetModule->setEmail("employee@example.org")
    ->setLocale("en_GB");
  $referenceId = $extranetModule->getReferenceId(); // $referenceId is our extranet reference ID (UUID, exactly 36 chars)

  // Store $referenceId in your database, you may need it later when you want to delete the user
  // Meanwhile, Ukey1 have just sent an email with the invitation link

  // Next steps?
  // - user clicks to the invitation link
  // - user signs in to Ukey1 gateway
  // - user is redirected back to homepage (or separated login page) of your app
  // - you can directly initiate a standard Connection request (and redirect to Ukey1)
  // - user is already logged in Ukey1, so they only must authorize your app
  // - That's it!

} catch (\Exception $e) {
  echo "Unfortunatelly, an error was occured: " . $e->getMessage();
  exit;
}
```

For install purposes (it means when no user is in your user management database), the owner of the app in Ukey1 dashboard is automatically authorized to log in to your app. Just log in like in case of a public app.

Please note that each environment is separate for this feature, so when you add new user on test environment, you have to add them again for production environment (and vice versa) if you need so.

## Example

Would you like a working example? You can download and try [ukey1-php-sdk-example](https://github.com/noo-zh/ukey1-php-sdk-example/).

## License

This code is released under the MIT license. Please see [LICENSE](https://github.com/asaritech/ukey1-php-sdk/blob/master/LICENSE) file for details.

## Contributing

If you want to become a contributor of this PHP SDK, please first contact us (see our email below). 
Please note we follow [[PSR-2](http://www.php-fig.org/psr/psr-2/)]. 
If you would like to work on another SDK (in your favorite language), we will glad to know about you too!

## Contact

Reporting of any [issues](https://github.com/asaritech/ukey1-php-sdk/issues) are appreciated. 
If you want to contribute or you have found a critical bug, please write us directly to [developers@asaritech.com](mailto:developers@asaritech.com).
