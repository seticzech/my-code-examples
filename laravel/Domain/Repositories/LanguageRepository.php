<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\Language;
use App\Exceptions\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;


class LanguageRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Language::class);
    }


    /**
     * @param string $name
     * @param string $adverb
     * @param string $code
     * @param string $locale
     * @return Language
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $name, int $sortOrder = null): Language
    {
        if ($sortOrder === null) {
            $sortOrder = $this->getLatestSortOrderValue() + 1;
        }

        $language = new Language($name, $sortOrder);

        $this->em->persist($language);

        return $language;
    }


    /**
     * @return array|Language[]
     */
    public function findAll()
    {
        return $this->er->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']);
    }


    /**
     * @param string $code
     * @return object|Language|null
     */
    public function findByCode(string $code)
    {
        return $this->er->findOneBy(['code' => $code]);
    }


    /**
     * @param int $id
     * @return object|Language|null
     */
    public function findById(int $id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    public function getLatestSortOrderValue(): int
    {
        $language = $this->er->findOneBy([], ['sordOrder' => 'DESC']);

        return $language ? $language->getSortOrder() : 0;
    }


    /**
     * @param array $idList
     * @return LanguageRepository
     * @throws InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function reorder(array $idList): LanguageRepository
    {
        $criteria = Criteria::create();
        $languages = new ArrayCollection($this->findAll());
        $sortOrder = 1;

        foreach ($idList as $id) {
            /** @var Language $language */
            $language = $languages->matching($criteria->where(Criteria::expr()->eq('id', $id)))->first();
            if (!$language) {
                throw new InvalidArgumentException("Languages reorder failed, language with ID: '{$id}' not found.");
            }

            $language->setSortOrder($sortOrder);

            $this->em->persist($language);
            $this->em->flush($language);

            $sortOrder++;
        }

        return $this;
    }


    /**
     * @param Language $language
     * @param array $data
     * @return LanguageRepository
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(Language $language, array $data): LanguageRepository
    {
        $validKeys = ['name', 'adverb', 'code', 'flag', 'isoCode2', 'locale', 'pluralsCount', 'pluralsRules', 'sortOrder'];

        $language->fromArray($data, $validKeys);
        $this->em->persist($language);

        return $this;
    }

}
