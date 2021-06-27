A stateless user provider
=============================

This feature inspired by the awesome [Lexik JWT Authentication](https://github.com/lexik/LexikJWTAuthenticationBundle) bundle.

Stateless user provider help to create user instances from the JWT payload, avoiding the need to query the database more than once
or in cases user is an identity of first or third party system.

Configuring the user provider
-----------------------------

First, you need to config `istio_jwt_stateless` provider in `security.yaml`:

```yaml
# config/packages/security.yaml
security:
    providers:
      jwt:
        istio_jwt_stateless:
          class: App\Security\User # your user class, you can change it if you want.
```

Then, create a user class `istio_jwt_stateless.class` had set in config, in this case is `App\Security\User`, this class need to implement [StatelessUserInterface](/src/User/StatelessUserInterface.php).
This interface contains only a `fromPayload(array $payload)` method returns an instance of the class.

#### Sample implementation

```php
namespace App\Security;

use Istio\Symfony\JWTAuthentication\User\StatelessUserInterface;

final class User implements StatelessUserInterface
{
    //....
    
    public static function fromPayload(array $payload): static
    {
        $instance = new static();
        
        // use $payload to config your user instance.
        
        return $instance;
    }
}
```