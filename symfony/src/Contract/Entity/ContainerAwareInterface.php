<?php

namespace App\Contract\Entity;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{

    public function getContainer(): ContainerInterface;

    public function setContainer(ContainerInterface $container);

}
