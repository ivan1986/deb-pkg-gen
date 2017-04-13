<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ivan1986\DebBundle\Entity\SimplePackage.
 *
 * @ORM\Entity()
 */
class SimplePackage extends Package
{
    /**
     * @var Repository Репозиторий этого пакета
     *
     * @ORM\ManyToOne(targetEntity="Repository", cascade="all", inversedBy="packages")
     * @ORM\JoinColumn(name="repository_id", referencedColumnName="id")
     */
    protected $repository;

    /**
     * @param \Ivan1986\DebBundle\Entity\Repository $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \Ivan1986\DebBundle\Entity\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
