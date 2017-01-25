# Ukey1 SDK for PHP

This repository contains the open source PHP SDK that allows you to access the **[Ukey1 API](http://ukey.one)** from your PHP app.

## About Ukey1

[Ukey1](http://ukey.one) is *an aggregator of your user's social identities*. 
Ukey1 is also a [OAuth 2.0](https://oauth.net/2/) provider but what is more important, it connects all major identity providers 
(like [Google](https://developers.google.com/identity/) or [Facebook](https://developers.facebook.com/docs/facebook-login)) 
into one sophisticated solution. Read [more](http://ukey.one/).

### Ukey1 flow for this PHP SDK

1. User clicks to "sign-in" button
  - you may use our [unified sign-in button](https://github.com/asaritech/ukey1-signin-button)
2. SDK sends a connection request to our API and gets a unique Gateway URL
3. User is redirected to Ukey1 Gateway
4. User signs in using their favourite solution and authorizes your app
5. User is redirected back to predefined URL
6. SDK checks the result and gets a unique access token
7. That's it - user is authenticated (your app can make API calls to get user's data)

### RAML API specification

You can also download our [API specification](https://ukey1.nooledge.com/var/public/api.raml) in [RAML format](http://raml.org/).

## Requirements

- PHP 5.5
- [Guzzle](http://guzzlephp.org)

## Installation

The Ukey1 PHP SDK can be installed with [Composer](https://getcomposer.org/) (recommended option). Run this command:

```bash
$ composer require asaritech/ukey1-php-sdk
```

## Usage

First, you need your app credentials (`App ID` and `Secret Key`). 

Ukey1 uses advanced security methods, so its flow may be a little bit more complicated. 
But don't worry, this SDK is prepared to be as easy-to-use as possible.

### Sign-in / sign-up / log-in - all buttons in one

Your app may look like this:

```html
<html>
  <head>
    <!-- ... -->
    <link rel="stylesheet" type="text/css" href="https://gitcdn.xyz/repo/asaritech/ukey1-signin-button/master/css/ukey1-button.min.css" media="screen">
  </head>
  <body>
    <!-- ... -->
    <a href="login.php" class="ukey1-button">Sign in via Ukey1</a>
    <!-- ... -->
  </body>
</html>
```

### Connection request

Your script `login.php` makes a request to our endpoint `/auth/connect`.

```php
session_start();

use \Ukey1\App;
use \Ukey1\Endpoints\Authentication\Connect;
use \Ukey1\Generators\RandomString;

define("APP_ID", "your-app-id");
define("SECRET_KEY", "your-secret-key");

try {
  // Entity of your app
  $app = new App();
  $app->appId(APP_ID)
      ->secretKey(SECRET_KEY);

  // You need a request ID (no need to be unique but it's better)
  // It may be a random string or number
  // But it may also be your own reference ID
  // Maximum length is 128 chars
  $requestId = RandomString::generate(16); 

  // This is an URL for redirection back to the app
  // Do you know what is absolutely perfect?
  // - it may be unique
  // - it may contain query parameters and/or fragment
  $returnUrl = "http://example.org/login.php?action=check&user=XXX#fragment";

  // Endpoint module
  // Here is a list of possible grants:
  // - `access_token` (always default)
  // - `refresh_token` (optional, use only if you will really need to refresh `access_token` when expires)
  // - `email` (access to user's email)
  // - `image` (access to user's thumbnail)
  // NOTE: If you are eligible to use "!" (means a required value), you may use it with `email!` and `image!`
  $connectModule = new Connect($app);
  $connectModule->setRequestId($requestId)
         ->setReturnUrl($returnUrl)
         ->setScope([
           "access_token",
           "email",
           "image"
         ]);
  $connectModule->execute();
  $connectId = $connectModule->getId(); // $connectId is our reference ID (maximum length 128 chars)

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

URL will look like this: `http://example.org/login.php?action=check&user=XXX&_ukey1[request_id]={REQUEST_ID}&_ukey1[connect_id]={CONNECT_ID}&_ukey1[result]={RESULT}&_ukey1[signature]={SIGNATURE}#fragment` 
where `REQUEST_ID` is previously used `$requestId`, `CONNECT_ID` is previously used `$connectId`, `RESULT` may be *authorized*, *canceled* or *expired* and 
`SIGNATURE` is a security signature.

```php
session_start();

use \Ukey1\App;
use \Ukey1\Endpoints\Authentication\AccessToken;
use \Ukey1\Endpoints\Authentication\User;

try {
  $app = new App();
  $app->appId(APP_ID)
      ->secretKey(SECRET_KEY);

  // Endpoint module
  // You needs $requestId and $connectId that you previously stored in your database or session
  // WARNING: Don't use values from GET query
  $tokenModule = new AccessToken($app);
  $tokenModule->setRequestId($_SESSION["requestId"])
         ->setConnectId($_SESSION["connectId"]);
  $check = $tokenModule->execute(); // returns false if user cancels the request or it expires, otherwise returns true

  if ($check) {
    // Store access token in your database or session
    $_SESSION["accessToken"] = $tokenModule->getAccessToken();

    // You can now unset request ID and connect ID from session or your database
    unset($_SESSION["requestId"], $_SESSION["connectId"]);

    // Now you can read user's data
    $userModule = new User($app);
    $userModule->setAccessToken($_SESSION["accessToken"]);
    $userModule->execute();
    $user = $userModule->getUser();

    if ($user->check()) {
      // Store following ID in your database (maximum length 128 chars)
      $userId = $user->id();

      // User's fullname, firstname and surname
      $fullname = $user->fullname();
      $firstname = $user->firstname();
      $surname = $user->surname();

      // User's language (e.g. "en")
      $language = $user->language();

      // User's country (e.g. "USA")
      $country = $user->country();

      // User's email
      // WARNING: User may refuse to share their email with your app during authorization process
      // If you are eligible to use "!" (means a required value) in scopes, you will always get user's email
      $email = $user->email();

      // User's image
      // WARNING: User may refuse to share their image with your app during authorization process
      // If you are eligible to use "!" (means a required value) in scopes, you will always get user's image
      $imageSrc = $user->thumbnailUrl();
      
      // User may share their default image with your app (the first letter of firstname)
      // If you would like to detect this case, you can use following:
      $thumbnail = $user->thumbnailEntity();

      if (!$thumbnail->isDefault()) {
        $imageSrc = $thumbnail->url();
        //$thumbnail->download();
        //$thumbnail->width();
        //$thumbnail->height();

      } else {
        // Use your own icon
      }

    } else {
      // Meanwhile, the user canceled their consent in Ukey1 dashboard - i.e. you lost you access and need their authorization again
      // You should destroy session and logout the user
    }

    // Redirect to your secure page which is visible only for authorized users
    // It's a good practise to redirect user even if you want to keep them on the same page because it's better to hide all "_ukey1" query parameters.
    $urlOfSecuredAreaIfUserIsLoggedIn = "http://example.org/welcome.php";
    header("Location: {$urlOfSecuredAreaIfUserIsLoggedIn}");

  } else {
    // Authentication canceled...
  }

} catch (\Exception $e) {
  echo "Unfortunatelly, an error was occured: " . $e->getMessage();
  exit;
}
```

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
If you want to contribute or you have a critical security issue, please write us directly to [developers@asaritech.com](mailto:developers@asaritech.com).
