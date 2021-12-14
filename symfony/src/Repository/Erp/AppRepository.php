<?php

namespace App\Repository\Erp;

use App\Doctrine\Query\SortableNullsWalker;
use App\Entity\Erp\App;
use App\Entity\Erp\Language;
use App\Entity\Erp\Module;
use App\Entity\Erp\Translation;
use App\Entity\Erp\TranslationCustom;
use App\Repository\RepositoryAbstract;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

/**
 * @method findOneByCode(string $code): ?App
 */
class AppRepository extends RepositoryAbstract
{

    /**
     * @var string
     */
    protected static $entityClass = App::class;


    public function getTranslations(App $app, Language $language)
    {
        $fields = ['t.id', 'm.code AS module_code', 't.context', 't.code', 'COALESCE(tc.message, t.message) as message'];

        $qbl = $this->_em->createQueryBuilder();

        $qbl->select($fields)
            ->from(Translation::class, 't')
            ->leftJoin(Module::class, 'm', Expr\Join::WITH, 'm.id = t.module')
            ->leftJoin(Language::class, 'l', Expr\Join::WITH, 'l.id = t.language')
            ->leftJoin(TranslationCustom::class, 'tc', Expr\Join::WITH, 'tc.translation = t.id')
            ->where('l.iso_639_1 = :langCode')
            ->andWhere('t.app = :app')
            ->setParameter('langCode', $language->getIso6391())
            ->setParameter('app', $app)
            ->orderBy('m.code, t.context, t.code');

        // add NULL FIRST
        $query = $qbl->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);
        $query->setHint(SortableNullsWalker::HINT, [
            'm.code' => SortableNullsWalker::NULLS_FIRST,
        ]);

        return $query->execute();

    }

}
