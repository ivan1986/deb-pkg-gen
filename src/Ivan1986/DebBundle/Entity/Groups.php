<?php

namespace Ivan1986\DebBundle\Entity;

use FOS\UserBundle\Entity\Group as GroupUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ivan1986\DebBundle\Entity\Groups
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Entity\GroupsRepository")
 */
class Groups extends GroupUser
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}