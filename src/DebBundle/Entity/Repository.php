<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ivan1986\DebBundle\Form\Type\RepositoryType;
use Ivan1986\DebBundle\Service\Builder;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ivan1986\DebBundle\Entity\Repository.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Repository\RepositoryRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="repoType", type="string")
 * @ORM\DiscriminatorMap({"standart" = "Repository", "ppa" = "PpaRepository"})
 * @UniqueEntity(fields="name", message="Пакет с таким именем уже есть в системе")
 */
class Repository
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    //<editor-fold defaultstate="collapsed" desc="Bin Src флаги">
    /**
     * @var bool В репозитории есть бинарники
     *
     * @ORM\Column(name="`bin`", type="boolean")
     */
    protected $bin = true;

    /**
     * @var bool В репозитории есть исходный код
     *
     * @ORM\Column(name="`src`", type="boolean")
     */
    protected $src = true;

    /**
     * Set bin.
     *
     * @param bool $bin
     *
     * @return Repository
     */
    public function setBin($bin)
    {
        $this->bin = $bin;

        return $this;
    }

    /**
     * Get bin.
     *
     * @return bool
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Set src.
     *
     * @param bool $src
     *
     * @return Repository
     */
    public function setSrc($src)
    {
        $this->src = $src;

        return $this;
    }

    /**
     * Get src.
     *
     * @return bool
     */
    public function getSrc()
    {
        return $this->src;
    }

    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="Название пакете в системе">
    /**
     * @var string Имя репозитория
     *
     * @ORM\Column(name="`name`", type="string", length=150, unique=true)
     */
    protected $name;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Repository
     */
    public function setName($name)
    {
        $name = strtolower($name);
        $name = str_replace(' ', '-', $name);
        $name = str_replace('.', '-', $name);
        $name = preg_replace('/[^a-z0-9-]*/', '', $name);
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
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
     * @var string Адрес репозитория
     *
     * @ORM\Column(name="`repoString`", type="string")
     */
    protected $repoString;

    /**
     * Строки в файле deb репозитория.
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
     * Строка репозитория.
     *
     * @return string
     */
    public function getRepoString()
    {
        return $this->repoString;
    }

    public function getUrl()
    {
        return explode(' ', $this->repoString)[0];
    }

    /**
     * Разбирает строку репозитория.
     *
     * @param string $name
     * @return Repository
     */
    public function setRepoString($name)
    {
        $items = array_values(array_filter(explode(' ', $name)));
        if (isset($items[0]) && ($items[0] == 'deb' || $items[0] == 'deb-src')) {
            array_shift($items);
        }
        $this->repoString = implode(' ', $items);

        return $this;
    }

    /**
     * Проверяет валидность строки репозитория.
     *
     * @Assert\IsTrue(message = "Неверный формат строки репозитория")
     *
     * @return bool
     */
    public function isValidRepoString()
    {
        return $this->isValid();
    }

    protected function isValid()
    {
        $items = explode(' ', $this->repoString);

        if (count($items) < 2) {
            return false;
        }

        if (filter_var($items[0], FILTER_VALIDATE_URL) != $items[0]) {
            return false;
        }

        return true;
    }

    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="Ключ">
    /**
     * @var GpgKey Ключ
     *
     * @ORM\OneToOne(targetEntity="GpgKey")
     */
    protected $key;

    /**
     * Set key.
     *
     * @param GpgKey $key
     *
     * @return Repository
     */
    public function setKey(GpgKey $key = null)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key.
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
     * @var User Пользователь, создавший пакет
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="repositories")
     */
    protected $owner;

    /**
     * Set owner.
     *
     * @param User $owner
     *
     * @return Repository
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
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
     * @ORM\OneToMany(targetEntity="SimplePackage", mappedBy="repository", cascade={"remove"})
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

    /**
     * Класс формы для этого типа пакета.
     *
     * @return string
     */
    public function getFormClass()
    {
        return RepositoryType::class;
    }

    public function buildPackages(Builder $builder, ObjectManager $manager)
    {
        $data = $builder->build($this);
        if (!$data) {
            return false;
        }
        $package = new SimplePackage();
        $package->setContent($data['content']);
        $package->setFile($data['file']);
        $package->setInfo($data['finfo']);
        $package->setRepository($this);
        $manager->persist($package);

        return true;
    }

    public function getUserName()
    {
        return $this->getOwner() ? $this->getOwner()->getUsernameCanonical() : '';
    }

    public function getKeyId()
    {
        return $this->getKey() ? $this->getKey()->getId() : '';
    }

    public function getType()
    {
        return 'standart';
    }

    public function getPackagesWithLinks()
    {
        foreach ($this->packages as $pkg) {
            yield $pkg->getName() => $pkg;
        }
    }
}
