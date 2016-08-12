<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PackageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PackageRepository extends EntityRepository
{

    /**
     * Получить все пакеты
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQB()
    {
        return $this->createQueryBuilder('p');
    }

    /**
     * @return Package[]
     */
    public function getSystem()
    {
        return $this->_em->createQuery('SELECT p FROM Ivan1986\DebBundle\Entity\SysPackage AS p')->getResult();
    }

    /**
     * Список пакетов основного репозитория
     * @return Package[]
     */
    public function mainRepo()
    {
        return $this->_em->createQuery('SELECT p FROM Ivan1986\DebBundle\Entity\Package')->getResult();
    }

    /**
     * Получить для пользователя
     *
     * @param User $user
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByUser(User $user=null)
    {
        $qb = $this->createQueryBuilder('p');
        if ($user)
            $qb->andWhere('p.user = ?1')->setParameter(1, $user->getId());
        return $qb;
    }

    /**
     * Получить по ID с проверкой пользователя
     *
     * @param $id ID репозитория
     * @param User $user пользователь
     * @return object
     */
    public function getByIdAndCheckUser($id, User $user)
    {
        return $this->findOneBy(array('id' => $id, 'user' => $user->getId()));
    }

}
