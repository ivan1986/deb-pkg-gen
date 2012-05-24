<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\UserBundle\Entity\User;

/**
 * Ivan1986\DebBundle\Entity\Repository
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Entity\RepositoryRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="repoType", type="string")
 * @ORM\DiscriminatorMap({"standart" = "Repository"})
 */
class Repository
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

    //<editor-fold defaultstate="collapsed" desc="Основные переменные, описывающие репозиторий">
    /**
     * @var string $url Адрес репозитория
     *
     * @ORM\Column(name="url", type="string")
     */
    protected $url;

    /**
     * @var string $name Релиз (stable,testing,sid, natty,oneiric)
     *
     * @ORM\Column(name="release", type="string")
     */
    protected $release;

    /**
     * @var string $components Компоненты
     *
     * @ORM\Column(name="components", type="array")
     */
    protected $components;

    /**
     * @var boolean $bin В репозитории есть бинарники
     *
     * @ORM\Column(name="bin", type="boolean")
     */
    protected $bin = true;

    /**
     * @var boolean $src В репозитории есть исходный код
     *
     * @ORM\Column(name="src", type="boolean")
     */
    protected $src = true;

    /**
     * Set url
     *
     * @param string $url
     * @return Repository
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set release
     *
     * @param string $release
     * @return Repository
     */
    public function setRelease($release)
    {
        $this->release = $release;
        return $this;
    }

    /**
     * Get release
     *
     * @return string
     */
    public function getRelease()
    {
        return $this->release;
    }

    /**
     * Set components
     *
     * @param array $components
     * @return Repository
     */
    public function setComponents($components)
    {
        $this->components = $components;
        return $this;
    }

    /**
     * Get components
     *
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * Set bin
     *
     * @param boolean $bin
     * @return Repository
     */
    public function setBin($bin)
    {
        $this->bin = $bin;
        return $this;
    }

    /**
     * Get bin
     *
     * @return boolean
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Set src
     *
     * @param boolean $src
     * @return Repository
     */
    public function setSrc($src)
    {
        $this->src = $src;
        return $this;
    }

    /**
     * Get src
     *
     * @return boolean
     */
    public function getSrc()
    {
        return $this->src;
    }
    //</editor-fold>

    /**
     * @var string $name Имя репозитория
     *
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * Set name
     *
     * @param string $name
     * @return Repository
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function pkgName()
    {
        return 'repo-'.$this->getName();
    }

    public function getDebStrings()
    {
        $str = $this->getUrl().' '.$this->getRelease().' '.implode(' ', $this->getComponents());
        return
            ($this->bin ? ('deb '.$str."\n") : '').
            ($this->src ? ('deb-src '.$str."\n") : '');
    }

    //<editor-fold defaultstate="collapsed" desc="Ключ">
    /**
     * @var GpgKey $key Ключ
     *
     * @ORM\OneToOne(targetEntity="GpgKey")
     */
    protected $key;

    /**
     * Set key
     *
     * @param GpgKey $key
     * @return Repository
     */
    public function setKey(GpgKey $key = null)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Get key
     *
     * @return GpgKey
     */
    public function getKey()
    {
        return $this->key;
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="Пользователь">
    /**
     * @var User $owner Пользователь, создавший пакет
     *
     * @ORM\ManyToOne(targetEntity="Ivan1986\UserBundle\Entity\User")
     */
    protected $owner;


    /**
     * Set owner
     *
     * @param User $owner
     * @return Repository
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }
    //</editor-fold>


}