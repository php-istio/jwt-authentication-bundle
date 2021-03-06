<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Fixtures;

use Symfony\Component\HttpFoundation\Response;

class SecureController
{
    public function __invoke()
    {
        return new Response('', 200);
    }
}
