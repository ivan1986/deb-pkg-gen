<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\ObjectManager;
use Ivan1986\DebBundle\Model\DistList;
use Ivan1986\DebBundle\Util\Builder;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;
use Ivan1986\DebBundle\Model\GpgLoader;
use Ivan1986\DebBundle\Form\PpaRepositoryType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ivan1986\DebBundle\Entity\PpaRepository
 *
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Entity\RepositoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class PpaRepository extends Repository
{

    public function setRepoString($string)
    {
        if (strpos($string, 'ppa:') !== 0)
            $string = 'ppa:'.$string;
        if (strpos($string, '/') === false)
            $string = $string.'/ppa';
        if ($this->repoString != $string)
            $this->distrs = null;
        return parent::setRepoString($string);
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updName()
    {
        if (empty($this->name))
            $this->setName(str_replace(array(':', '/'), '-', $this->repoString));
    }

    private $status = false;

    public function getDebStrings()
    {
        if ($this->status == 'all')
        {
            $strings = '';
            foreach ($this->distrs->all as $dist)
            {
                $str = $this->getPpaUrl().' '.($dist).' main';
                $strings .= ($this->bin ? ('deb '.$str."\n") : '').
                            ($this->src ? ('deb-src '.$str."\n") : '');
            }
            return $strings;
        }
        $str = $this->getPpaUrl().' '.($this->distrs->{$this->status}).' main';
        return
            ($this->bin ? ('deb '.$str."\n") : '').
            ($this->src ? ('deb-src '.$str."\n") : '');
    }

    public function pkgName()
    {
        return 'repo-'.$this->getName().'-'.$this->status;
    }

    public function buildPackages(Builder $builder, ObjectManager $manager)
    {
        if (!$this->distrs)
            return false;
        foreach(array('lts', 'stable', 'testing', 'all') as $v)
        {
            $this->status = $v;
            $res = parent::buildPackages($builder, $manager);
            if (!$res)
                return false;
        }
        return true;
    }

    protected function isValid()
    {
        if (strpos($this->repoString, 'ppa:') !== 0)
            return false;
        if (strpos($this->repoString, '/', strpos($this->repoString, '/')+1) !== false)
            return false;
        return true;
    }

    public function getPpaUrl()
    {
        $arr = explode('/', substr($this->repoString, 4));
        if (count($arr) != 2)
            return '';
        return 'http://ppa.launchpad.net/'.$arr[0].'/'.$arr[1].'/ubuntu/';
    }

    private function getPpaPage()
    {
        $arr = explode('/', substr($this->repoString, 4));
        if (count($arr) != 2)
            return '';
        return 'https://launchpad.net/~'.$arr[0].'/+archive/'.$arr[1];
    }

    /**
     * Проверяет валидность строки репозитория
     *
     * @Assert\IsTrue(message = "Такого репозитория нет на launchpad.net")
     * @return bool
     */
    public function isExistRepo()
    {
        if ($this->getPpaUrl() == '')
            return false;
        $client = new \GuzzleHttp\Client();
        //репозиторий существует, заодно получим ключ
        if ($client->get($this->getPpaUrl())->getStatusCode() != 200)
            return false;
        return $this->getKeyFromLaunchpad();
    }

    private function getKeyFromLaunchpad()
    {
        $client = new \GuzzleHttp\Client(['defaults' => [
            'verify' => 'false'
        ]]);
        $data = $client->get($this->getPpaPage())->getBody();
        if (!$data)
            return false;
        $matches = array();
        preg_match('#<code>\d+R/(.*)</code>#is', $data, $matches);
        if (empty($matches[1]))
            return false;
        $keyId = $matches[1];

        $r = $this->container->get('doctrine')->getRepository('Ivan1986DebBundle:GpgKey');
        $key = $r->findOneBy(array('id' => $keyId));
        if ($key)
        {
            $this->setKey($key);
            return true;
        }
        try {
            $key = GpgLoader::getFromServer($keyId, 'keyserver.ubuntu.com');
        } catch(GpgNotFoundException $e) {
            return false;
        }
        $em = $this->container->get('doctrine')->getManager();
        /** @var $em \Doctrine\ORM\EntityManager */
        $em->persist($key);
        $this->setKey($key);
        return true;
    }

    public function getFormClass()
    {
        return PpaRepositoryType::class;
    }

    /**
     * @var DistList $distrs Список дистрибутивов, соответствующих
     *
     * @ORM\Column(name="distrs", type="object")
     */
    protected $distrs;

    /**
     * @param DistList $distrs
     */
    public function setDistrs($distrs)
    {
        $this->distrs = $distrs;
    }

    /**
     * @return DistList
     */
    public function getDistrs()
    {
        return $this->distrs ? $this->distrs : new DistList();
    }
    public function getType()
    {
        return "ppa";
    }

    public function getForApt() {
        $links = [];
        foreach($this->packages as $pkg)
        {
            $name = str_replace('repo-'.$this->getName().'-', '', $pkg->getName());
            $links[] = '<a href="apt:'.$pkg->getName().'">'.$name.'</a>';
        }
        return join(' ', $links);
    }

    public function getLinks($router)
    {
        $links = [];
        foreach($this->packages as $pkg)
        {
            $name = str_replace('repo-'.$this->getName().'-', '', $pkg->getName());
            $links[] = '<a href="'.$router->generate('Package', array('name' => $pkg->getFile())).'">'.$name.'</a>';
        }
        return join(' ', $links);
    }

}
