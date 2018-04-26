# CHANGELOG

Note that the Ukey1 PHP SDK follows [SemVer](http://semver.org/).


## 3.x

- 3.0.4 (2018-04-26)
  - Add possibility to disable local verification of `request_id` and `connect_id`
  - Add possibility to circumvent GET parameters with custom input (e.g. especially when you use client-server combination for handling Ukey1 Gateway response and send params from client to server via POST method)

- 3.0.3 (2018-04-23)
  - Add feature `Private users` for premium apps

- 3.0.2 (2017-11-14)
  - Fix issue when granted scope is null

- 3.0.1 (2017-11-14)
  - Fix issue with missing `$_SERVER["REQUEST_SCHEME"]`, so it's no longer needed to call `App::setDomain("http://example.org");`

- 3.0.0 (2017-10-05)
  - Support for new Ukey1 APIv2 (please note that APIv1 will be terminated in November 15, 2017)

## 2.x

- 2.0.0 (2017-02-12)
  - Hash algorithm for the request signature has been changed (all older versions are incompatible now!!!)

## 1.x

- 1.0.1 (2017-02-06)
  - Redirections allowed by default

- 1.0.0 (2017-01-11)
  - Initial release