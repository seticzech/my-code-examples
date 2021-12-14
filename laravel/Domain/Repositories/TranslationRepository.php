<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\Language;
use App\Domain\Entities\Translation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;


class TranslationRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Translation::class);
    }


    /**
     * @return array|Translation[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @param string $languageCode
     * @return array|Translation[]
     */
    public function findByLanguageCode(string $languageCode)
    {
        $qbl = $this->em->createQueryBuilder();

        $qbl->select('t')
            ->from(Language::class, 'l')
            ->leftJoin(Translation::class, 't', Expr\Join::WITH, 't.language = l.id')
            ->where('l.code = :langCode')
            ->setParameter('langCode', $languageCode)
            ->getQuery();

        return $qbl->getQuery()->execute();
    }

}
