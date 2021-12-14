<?php

namespace App\Service\Erp\Sso\Customer;

use App\Entity\Erp\User;
use App\Exception\BadRequestException;
use App\Exception\ForbiddenException;
use App\Service\Erp\Sso\Contract\SsoServiceInterface;
use App\Service\Erp\Sso\SsoService;

class Forfin extends SsoService implements SsoServiceInterface
{

    const USER_ID_MASK = "0888";


    protected function verifyPayload(): bool
    {
        $attrs = ["given_name", "family_name", "email", "sun"];

        foreach ($attrs as $attr) {
            if (!array_key_exists($attr, $this->payload)) {
                throw new BadRequestException("SSO payload verification failed, missing attribute '{$attr}'");
            }
        }

        if (strpos($this->payload->sun, self::USER_ID_MASK) !== 0) {
            throw new ForbiddenException("User ID doesn't match the specified mask 0888*");
        }

        return true;
    }

    protected function registerUser(): User
    {
        $user = parent::registerUser();

        $user
            ->setFirstName($this->payload->given_name)
            ->setLastName($this->payload->family_name);

        return $user;
    }

    protected function updateUser($user)
    {
        $ids = $user->getUserCode()
            ? explode(",", $user->getUserCode())
            : [];

        foreach ($ids as $id) {
            if ($this->payload->sun === $id) {
                return $user;
            }
        }

        $ids = empty($ids)
            ? [$this->payload->sun]
            : array_merge($ids, [$this->payload->sun]);

        $user->setUserCode(implode(",", $ids));

        return $user;
    }

}
