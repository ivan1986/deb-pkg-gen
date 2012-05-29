<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\User;
use Ivan1986\DebBundle\Exception\ParseRepoStringException;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\Column(name="`url`", type="string")
     */
    protected $url;

    /**
     * @var string $name Релиз (stable,testing,sid, natty,oneiric)
     *
     * @ORM\Column(name="`release`", type="string")
     */
    protected $release;

    /**
     * @var string $components Компоненты
     *
     * @ORM\Column(name="`components`", type="array")
     */
    protected $components;

    /**
     * @var boolean $bin В репозитории есть бинарники
     *
     * @ORM\Column(name="`bin`", type="boolean")
     */
    protected $bin = true;

    /**
     * @var boolean $src В репозитории есть исходный код
     *
     * @ORM\Column(name="`src`", type="boolean")
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
        return $this->components ? $this->components : array();
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

    //<editor-fold defaultstate="collapsed" desc="Название пакете в системе">
    /**
     * @var string $name Имя репозитория
     *
     * @ORM\Column(name="`name`", type="string", unique=true)
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
        $this->name = str_replace('.', '-', $name);
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
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="Строка репозитория">

    /**
     * Строки в файле deb репозитория
     *
     * @return string
     */
    public function getDebStrings()
    {
        $str = $this->getRepoString();
        return
            ($this->bin ? ('deb '.$str."\n") : '').
            ($this->src ? ('deb-src '.$str."\n") : '');
    }

    /**
     * Строка репозитория
     *
     * @return string
     */
    public function getRepoString()
    {
        return trim($this->getUrl().' '.$this->getRelease().' '.implode(' ', $this->getComponents()));
    }

    /**
     * Разбирает строку репозитория
     *
     * @param string $name
     * @return Repository
     */
    public function setRepoString($string)
    {
        $items = explode(' ', $string);
        //устраняем лишние пробелы
        foreach($items as $k=>$v)
            if (empty($v))
                unset($items[$k]);
        $items = array_values($items);

        if (count($items) == 0)
            throw new ParseRepoStringException($string, 'Empty String', 0);
        if ($items[0] == 'deb' || $items[0] == 'deb-src')
            array_shift($items);
        if (count($items) == 0)
            throw new ParseRepoStringException($string, 'Not Found Url', 1);
        $this->setUrl(array_shift($items));

        if (count($items) == 0)
            throw new ParseRepoStringException($string, 'Not Found Release', 2);

        $this->setRelease(array_shift($items));

        //Если нету компонентов, то это упрощенный репозиторий, что тоже нормально
        if (count($items))
            $this->setComponents($items);

        return $this;
    }
    //</editor-fold>

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
     * @ORM\ManyToOne(targetEntity="User")
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

    /**
     * @var \DateTime Время редактирования репозитория
     *
     * @Gedmo\Timestampable
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @ORM\OneToMany(targetEntity="SimplePackage", mappedBy="repository")
     */
    protected $packages;

    public function __construct()
    {
        $this->packages = new ArrayCollection();
    }

    public function getPackages()
    {
        return $this->packages;
    }

}