<?php

namespace App\Contract\OAuth2;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserRepositoryInterface
{

    public function loadUserByUsername(string $username);

    public function refreshUser(UserInterface $user);

}
