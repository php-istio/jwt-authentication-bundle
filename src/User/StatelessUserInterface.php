<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\User;

use Symfony\Component\Security\Core\User\UserInterface;

interface StatelessUserInterface extends UserInterface
{
    public static function fromPayload(array $payload): static;
}
