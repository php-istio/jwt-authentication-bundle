Create custom user provider
=============================

In cases [stateless user provider](stateless-user-provider.md) not fit for your requirements, you can create your own [custom user provider](https://symfony.com/doc/current/security/user_provider.html#creating-a-custom-user-provider) 
implement [JWTPayloadAwareUserProviderInterface](/src/User/JWTPayloadAwareUserProviderInterface.php)
when you want to create user instance depend on JWT payload. 
This interface base on Symfony `UserProviderInterface` just add more optional arg `$payload` to `loadUserByIdentifier` method.

Configuring the user provider
-----------------------------
Config custom user provider in `security.yaml`:

```yaml
# config/packages/security.yaml
security:
    providers:
      jwt:
        id: App\Security\UserProvider # your user provider service id, change it if you want.
```

#### Sample implementation

```php
namespace App\Security;

use Istio\Symfony\JWTAuthentication\User\JWTPayloadAwareUserProviderInterface;

final class UserProvider implements JWTPayloadAwareUserProviderInterface {
    
    //.... 
    public function loadUserByIdentifier(string $identifier, array $payload = null) {
       // use $identifier and $payload to create instance of `UserInterface`.
    }
    
}
```
