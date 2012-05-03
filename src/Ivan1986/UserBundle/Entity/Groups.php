<?php

namespace Ivan1986\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ivan1986\UserBundle\Entity\Groups
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ivan1986\UserBundle\Entity\GroupsRepository")
 */
class Groups
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


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