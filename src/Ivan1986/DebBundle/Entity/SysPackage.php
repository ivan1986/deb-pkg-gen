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
}
