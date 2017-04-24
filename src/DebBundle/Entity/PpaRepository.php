<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Ivan1986\DebBundle\Form\Type\PpaRepositoryType;
use Ivan1986\DebBundle\Model\DistList;
use Ivan1986\DebBundle\Service\Builder;
use Ivan1986\DebBundle\Validator\Constraints as DebAssert;

/**
 * Ivan1986\DebBundle\Entity\PpaRepository.
 *
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Repository\RepositoryRepository")
 * @ORM\HasLifecycleCallbacks()
 * @DebAssert\LaunchpadExist()
 */
class PpaRepository extends Repository
{
    public function setRepoString($string)
    {
        if (strpos($string, 'ppa:') !== 0) {
            $string = 'ppa:'.$string;
        }
        if (strpos($string, '/') === false) {
            $string = $string.'/ppa';
        }
        if ($this->repoString != $string) {
            $this->distrs = null;
        }

        return parent::setRepoString($string);
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updName()
    {
        if (empty($this->name)) {
            $this->setName(str_replace([':', '/'], '-', $this->repoString));
        }
    }

    private $status = false;

    public function getDebStrings()
    {
        $strings = [];
        foreach ($this->getDistrs()[$this->status] as $dist) {
            $str = $this->getPpaUrl().' '.($dist).' main';
            if ($this->bin) {
                $strings[] = 'deb '.$str;
            }
            if ($this->src) {
                $strings[] = 'deb src '.$str;
            }
        }

        return implode("\n", $strings);
    }

    public function pkgName()
    {
        return 'repo-'.$this->getName().'-'.$this->status;
    }

    public function buildPackages(Builder $builder, ObjectManager $manager)
    {
        foreach (array_keys($this->getDistrs()) as $v) {
            $this->status = $v;
            $res = parent::buildPackages($builder, $manager);
            if (!$res) {
                return false;
            }
        }

        return true;
    }

    protected function isValid()
    {
        if (strpos($this->repoString, 'ppa:') !== 0) {
            return false;
        }
        if (strpos($this->repoString, '/', strpos($this->repoString, '/') + 1) !== false) {
            return false;
        }

        return true;
    }

    public function getPpaUrl()
    {
        $arr = explode('/', substr($this->repoString, 4));
        if (count($arr) != 2) {
            return '';
        }

        return 'http://ppa.launchpad.net/'.$arr[0].'/'.$arr[1].'/ubuntu/';
    }

    public function getPpaPage()
    {
        $arr = explode('/', substr($this->repoString, 4));
        if (count($arr) != 2) {
            return '';
        }

        return 'https://launchpad.net/~'.$arr[0].'/+archive/'.$arr[1];
    }

    public function getFormClass()
    {
        return PpaRepositoryType::class;
    }

    /**
     * @var array|DistList Список дистрибутивов, соответствующих
     *
     * @ORM\Column(name="distrs", type="object")
     */
    protected $distrs;

    /**
     * @param array $distrs
     */
    public function setDistrs($distrs)
    {
        $this->distrs = $distrs;
    }

    /**
     * @return array
     */
    public function getDistrs()
    {
        if ($this->distrs instanceof DistList) {
            return $this->distrs->convert();
        }

        return $this->distrs ?: [];
    }

    public function getType()
    {
        return 'ppa';
    }

    public function getPackagesWithLinks()
    {
        foreach ($this->packages as $pkg) {
            yield str_replace('repo-'.$this->getName().'-', '', $pkg->getName()) => $pkg;
        }
    }
}
