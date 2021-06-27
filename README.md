# JWT Authentication Bundle

![unit tests](https://github.com/php-istio/jwt-authentication-bundle/actions/workflows/unit-tests.yml/badge.svg)
![coding standards](https://github.com/php-istio/jwt-authentication-bundle/actions/workflows/coding-standards.yml/badge.svg)
[![codecov](https://codecov.io/gh/php-istio/jwt-authentication-bundle/branch/main/graph/badge.svg?token=ZVD9RJBHY3)](https://codecov.io/gh/php-istio/jwt-authentication-bundle)
[![Latest Stable Version](http://poser.pugx.org/php-istio/jwt-authentication-bundle/v)](https://packagist.org/packages/php-istio/jwt-authentication-bundle)

## About

The Symfony bundle provides JWT authentication for request forwarded by Istio sidecar. 

> To use this bundle, make sure your K8S application pod had injected Istio sidecar and [RequestAuthentication](https://istio.io/latest/docs/reference/config/security/request_authentication/) CRD had configured, if not your application **IS NOT SECURE**.

The difference between this bundle and the awesome [Lexik JWT Authentication](https://github.com/lexik/LexikJWTAuthenticationBundle) bundle is it's **NOT** validate JWT token because Istio sidecar proxy had validated before forward request to your application,
so that your application don't need to hold public key and double validate JWT token.

## Requirements

PHP versions:

+ PHP 8.0

Symfony versions:

+ Symfony 5.3

## Installation

```shell
composer require php-istio/jwt-authentication-bundle
```

## Configuration

Enable [the authenticator manager](https://symfony.com/doc/current/security/authenticator_manager.html) setting:

```yaml
# config/packages/security.yaml
security:
  enable_authenticator_manager: true
  # ...
```

Configure your `config/packages/security.yaml`:

```yaml
security:
  enable_authenticator_manager: true
  access_control: 
    - path: ^/
      roles: IS_AUTHENTICATED_FULLY
  firewalls:
    #...
    main:
      stateless: true
      istio_jwt_authenticator:
        - issuer: issuer_1 # Required
          user_identifier_claim: sub #Default is `sub` claim
          origin_token_headers: [authorization] #Required at least once of `origin_token_headers`, `origin_token_query_params` or `base64_headers`. Use this option when your Istio JWTRule CRD using `forwardOriginalToken`.
          origin_token_query_params: [token] #Use this option when your Istio JWTRule CRD using `forwardOriginalToken` and your JWT token in query param.
          base64_headers: [x-istio-jwt-payload] # Use this option when your Istio JWTRule CRD using `outputPayloadToHeader`.
```

In case your application have multi issuers:

```yaml
#....
    main:
      stateless: true
      istio_jwt_authenticator:
        - issuer: issuer_1
          origin_token_headers: [authorization]
        - issuer: issuer_2
          user_identifier_claim: aud
          base64_headers: [x-istio-jwt-payload]
        #....
```


## Usages

Generate mock JWT token forwarded by Istio sidecar:

```shell
payload='{"issuer":"issuer_1", "sub": "test"}'; \
base64_payload=$(echo -n $payload | base64 -); \
origin_token=$(echo "header.$base64_payload.signature")
```

You can test authenticate origin token with curl:

```shell
curl -H "Authorization: $origin_token" http://localhost/
```

Or authenticate base64 payload header:

```shell
curl -H "X-Istio-JWT-Payload: $base64_header" http://localhost/
```

## Further readings:

+ [Get JWT payload of authenticated user](src/Resources/doc/stateless-user-provider.md)
+ [Use stateless user provider](src/Resources/doc/stateless-user-provider.md)
+ [Create custom user provider](src/Resources/doc/create-custom-user-provider.md)

## Credits

+ [Minh Vuong](https://github.com/vuongxuongminh)