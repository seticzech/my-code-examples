<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Base\System\File as SystemFile;
use App\Domain\Entities\File;
use App\Domain\Entities\User;
use Doctrine\ORM\EntityManager;


class FileRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(File::class);
    }


    /**
     * @param User $user
     * @param string $name
     * @param string $mimeType
     * @param int $size
     * @return File
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(User $user, string $name, string $mimeType, int $size): File
    {
        $entity = new File($user, $name, $mimeType, $size);

        $this->em->persist($entity);

        return $entity;
    }


    /**
     * @param File $file
     * @return FileRepository
     * @throws \App\Exceptions\SystemException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(File $file)
    {
        $this->em->remove($file);
        $this->em->flush($file);

        $sysFile = new SystemFile($file->getRealName());
        if ($sysFile->isExists()) {
            $sysFile->remove(true);
        }

        return $this;
    }


    /**
     * @param array $files
     * @return $this
     * @throws \App\Exceptions\SystemException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->delete($file);
        }

        return $this;
    }


    /**
     * @param File $file
     * @param string $newPath
     * @return FileRepository
     * @throws \App\Exceptions\SystemException
     * @throws \Doctrine\ORM\ORMException
     */
    public function move(File $file, string $newPath)
    {
        $sysFile = new SystemFile($file->getRealName());
        $sysFile->move($newPath);

        $file->setPath($sysFile->getPath());

        $this->em->persist($file);

        return $this;
    }


    /**
     * @return array|File[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @param int $id
     * @return object|File|null
     */
    public function findById(int $id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    /**
     * @param array $ids
     * @return object[]|File[]
     */
    public function findByIds(array $ids)
    {
        return $this->er->findBy(['id' => $ids]);
    }


    /**
     * @param File $file
     * @param array $data
     * @return FileRepository
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(File $file, array $data): FileRepository
    {
        $validKeys = ['name', 'partialUpload', 'path', 'uploadedAt'];

        $file->fromArray($data, $validKeys);

        $this->em->persist($file);

        return $this;
    }

}
