<?php

namespace App\Repository\Sys;

use App\Entity\Sys\Tenant;
use App\Repository\RepositoryAbstract;

class TenantRepository extends RepositoryAbstract
{

    protected static $entityClass = Tenant::class;


    public function create(): Tenant
    {
        return new Tenant();
    }

    public function test()
    {
        dd('test');
    }

}
