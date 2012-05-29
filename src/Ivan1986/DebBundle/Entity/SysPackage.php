<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ivan1986\DebBundle\Entity\SysPackage
 *
 * @ORM\Table("Package")
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Entity\PackageRepository")
 */
class SysPackage extends Package
{
    /**
     * @var User Репозиторий этого пакета
     *
     * @ORM\OneToOne(targetEntity="Ivan1986\DebBundle\Entity\User")
     */
    protected $user;

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
