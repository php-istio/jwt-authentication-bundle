# JWT Authentication Bundle

![unit tests](https://github.com/php-istio/jwt-authentication-bundle/actions/workflows/unit-tests.yml/badge.svg)
![coding standards](https://github.com/php-istio/jwt-authentication-bundle/actions/workflows/coding-standards.yml/badge.svg)
[![codecov](https://codecov.io/gh/php-istio/jwt-authentication-bundle/branch/main/graph/badge.svg?token=ZVD9RJBHY3)](https://codecov.io/gh/php-istio/jwt-authentication-bundle)
[![Latest Stable Version](http://poser.pugx.org/php-istio/jwt-authentication-bundle/v)](https://packagist.org/packages/php-istio/jwt-authentication-bundle)

## About

This bundle provides JWT authentication for request forwarded by Istio sidecar. 

> To use this bundle, ensure your application container had injected Istio sidecar and Istio [RequestAuthentication](https://istio.io/latest/docs/reference/config/security/request_authentication/) CRD had configured, if not your application **IS NOT SECURE**.

## Requirements

PHP versions:

+ PHP 8.0

Symfony versions:

+ Symfony 5.3

## Installation

```shell
composer require php-istio/jwt-authentication-bundle
```

## Document



## Credits

+ [Minh Vuong](https://github.com/vuongxuongminh)