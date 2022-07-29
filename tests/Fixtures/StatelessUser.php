<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Fixtures;

use Istio\Symfony\JWTAuthentication\User\StatelessUserInterface;

class StatelessUser implements StatelessUserInterface
{
    public array $payload;

    public static function fromPayload(array $payload): static
    {
        $instance = new static();
        $instance->payload = $payload;

        return $instance;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUsername() method.
    }
}
