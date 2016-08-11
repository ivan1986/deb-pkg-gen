<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Ivan1986\DebBundle\Entity\SysPackage;
use Ivan1986\DebBundle\Entity\LinkPackage;

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
        $data = $this->_em->createQuery('SELECT p FROM Ivan1986\DebBundle\Entity\Package AS p
            WHERE p INSTANCE OF Ivan1986\DebBundle\Entity\SimplePackage')->getResult();
        return array_merge($data, $this->getSystem());
    }

    /**
     * Отмечает один пакет для проверки
     */
    public function markOneForTest()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->update('Ivan1986\DebBundle\Entity\LinkPackage', 'p');
        $qb->set('p.checked', LinkPackage::NOT_CHECKED);
        $qb->where('p.checked = ?1')->setParameter(1, LinkPackage::CHECK_NOW);
        $qb->getQuery()->execute();
        $first = $this->findOneBy(array('checked' => LinkPackage::NOT_CHECKED));
        if (!$first)
            return false;
        /** @var $first LinkPackage */
        $first->setChecked(LinkPackage::CHECK_NOW);
        $this->_em->persist($first);
        $this->_em->flush();
        return true;
    }

    /**
     * Устанавливает результат проверки
     */
    public function setResultForTest($status)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->update('Ivan1986\DebBundle\Entity\LinkPackage', 'p');
        $qb->set('p.checked', $status);
        $qb->where('p.checked = ?1')->setParameter(1, LinkPackage::CHECK_NOW);
        $qb->getQuery()->execute();
    }

    /**
     * Тестовый пакет
     */
    public function testRepo()
    {
        $data = $this->_em->createQuery('SELECT p FROM Ivan1986\DebBundle\Entity\LinkPackage AS p
            WHERE p.checked = ?1')->setParameter(1, LinkPackage::CHECK_NOW)->getResult();
        return $data;
    }

    /**
     * Проверенные пакеты
     */
    public function linkRepo()
    {
        $data = $this->_em->createQuery('SELECT p FROM Ivan1986\DebBundle\Entity\LinkPackage AS p
            WHERE p.checked = ?1')->setParameter(1, LinkPackage::CHECK_YES)->getResult();
        return $data;
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
