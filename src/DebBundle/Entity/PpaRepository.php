<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;
use Ivan1986\DebBundle\Form\Type\PpaRepositoryType;
use Ivan1986\DebBundle\Model\DistList;
use Ivan1986\DebBundle\Model\GpgLoader;
use Ivan1986\DebBundle\Service\Builder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ivan1986\DebBundle\Entity\PpaRepository.
 *
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Repository\RepositoryRepository")
 * @ORM\HasLifecycleCallbacks()
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
            if ($this->bin)
                $strings[] = 'deb '.$str;
            if ($this->src)
                $strings[] = 'deb src '.$str;
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

    private function getPpaPage()
    {
        $arr = explode('/', substr($this->repoString, 4));
        if (count($arr) != 2) {
            return '';
        }

        return 'https://launchpad.net/~'.$arr[0].'/+archive/'.$arr[1];
    }

    /**
     * Проверяет валидность строки репозитория.
     *
     * @Assert\IsTrue(message = "Такого репозитория нет на launchpad.net")
     *
     * @return bool
     */
    public function isExistRepo()
    {
        if ($this->getPpaUrl() == '') {
            return false;
        }
        $client = new \GuzzleHttp\Client(['http_errors' => false]);
        //репозиторий существует, заодно получим ключ
        if ($client->get($this->getPpaUrl())->getStatusCode() != 200) {
            return false;
        }

        return $this->getKeyFromLaunchpad();
    }

    private function getKeyFromLaunchpad()
    {
        $client = new \GuzzleHttp\Client(['defaults' => [
            'verify' => 'false',
        ]]);
        $data = $client->get($this->getPpaPage())->getBody();
        if (!$data) {
            return false;
        }
        $matches = [];
        preg_match('#<code>\d+R/(.*)</code>#is', $data, $matches);
        if (empty($matches[1])) {
            return false;
        }
        $keyId = $matches[1];

        $r = $this->container->get('doctrine')->getRepository('Ivan1986DebBundle:GpgKey');
        $key = $r->findOneBy(['id' => $keyId]);
        if ($key) {
            $this->setKey($key);

            return true;
        }
        try {
            $key = GpgLoader::getFromServer($keyId, 'keyserver.ubuntu.com');
        } catch (GpgNotFoundException $e) {
            return false;
        }
        $em = $this->container->get('doctrine')->getManager();
        /* @var $em \Doctrine\ORM\EntityManager */
        $em->persist($key);
        $this->setKey($key);

        return true;
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
