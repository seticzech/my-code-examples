<?php

namespace App\Repository\Erp;

use App\Entity\Erp\Language;
use App\Repository\RepositoryAbstract;

class LanguageRepository extends RepositoryAbstract
{

    /**
     * @var string
     */
    protected static $entityClass = Language::class;


    /**
     * @param string $isoCode
     *
     * @return Language|object|null
     */
    public function findOneByIso_639_1(string $isoCode): ?Language
    {
        return $this->findOneBy(['iso_639_1' => $isoCode]);
    }

}
