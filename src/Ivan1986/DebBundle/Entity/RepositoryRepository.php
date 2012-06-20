<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Ivan1986\DebBundle\Exception\ParseRepoStringException;

/**
 * RepositoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RepositoryRepository extends EntityRepository
{

    /**
     * Получить репозитории пользователя
     *
     * @param User $user
     * @return array
     */
    public function getByUser(User $user)
    {
        return $this->findBy(array('owner' => $user->getId()));
    }

    /**
     * Получить репозиторий по ID с проверкой пользователя
     *
     * @param $id ID репозитория
     * @param User $user пользователь
     * @return object
     */
    public function getByIdAndCheckUser($id, User $user)
    {
        return $this->findOneBy(array('id' => $id, 'owner' => $user->getId()));
    }

    public function getNewAndUpdated()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('r, p');
        $qb->leftJoin('r.packages', 'p');
        $qb->where('p.id IS NULL');
        $qb->orWhere('p.created < r.updated');
        return $qb->getQuery()->getResult();
    }

    public function getPpaForScan($onlyNew)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('r');
        if ($onlyNew)
            $qb->where('r.distrs IS NULL');
        return $qb->getQuery()->getResult();
    }


}