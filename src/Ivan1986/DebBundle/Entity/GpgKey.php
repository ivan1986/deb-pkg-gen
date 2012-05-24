<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ivan1986\DebBundle\Entity\GpgKey
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Entity\GpgKeyRepository")
 */
class GpgKey
{
    /**
     * @var string $id
     *
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string gpg data of key
     * @ORM\Column(name="gpg", type="blob")
     */
    protected $data;

    /**
     * @var string gpg data of key
     * @ORM\Column(name="fingerprint", type="blob")
     */
    protected $fingerprint;

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     * @return GpgKey
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set data
     *
     * @param blob $data
     * @return GpgKey
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return blob 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set fingerprint
     *
     * @param blob $fingerprint
     * @return GpgKey
     */
    public function setFingerprint($fingerprint)
    {
        $this->fingerprint = $fingerprint;
        return $this;
    }

    /**
     * Get fingerprint
     *
     * @return blob 
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }
}