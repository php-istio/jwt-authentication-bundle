Get JWT payload of authenticated user
=============================

You can get JWT payload of authenticated user via `jwt_payload` attribute in security token.

#### Sample:

```php
<?php

namespace App\Services;

use Symfony\Component\Security\Core\Security;

class MyService {

    public function __construct(Security $security) {
        $security->getToken()->getAttribute('jwt_payload');
    }
    
}
```


