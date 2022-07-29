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

final class StatelessUserProvider implements JWTPayloadAwareUserProviderInterface
{
    public function __construct(private string $statelessUserClass)
    {
        if (false === $this->supportsClass($this->statelessUserClass)) {
            throw new \InvalidArgumentException(sprintf('Stateless user class must be implement: `%s`', StatelessUserInterface::class));
        }
    }

    public function loadUserByIdentifier(string $identifier, array $payload = null): UserInterface
    {
        if (null === $payload) {
            throw new \LogicException(sprintf('`$payload` must be set when use %s!', self::class));
        }

        static $cache = [];
        $key = sha1(var_export($payload, true));

        if (!isset($cache[$key])) {
            return $cache[$key] = call_user_func([$this->statelessUserClass, 'fromPayload'], $payload);
        }

        return $cache[$key];
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return is_a($class, StatelessUserInterface::class, true);
    }
}
