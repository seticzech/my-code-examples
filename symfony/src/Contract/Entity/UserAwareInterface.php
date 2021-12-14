<?php

namespace App\Contract\Entity;

use App\Entity\Erp\User;

interface UserAwareInterface
{

    /**
     * @return User
     */
    public function getUser(): ?User;

    /**
     * @param User $value
     *
     * @return $this
     */
    public function setUser(User $value);

}
