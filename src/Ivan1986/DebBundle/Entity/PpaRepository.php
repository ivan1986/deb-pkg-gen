<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\ObjectManager;
use Ivan1986\DebBundle\Util\Builder;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;
use Ivan1986\DebBundle\Model\GpgLoader;
use Anchovy\CURLBundle\CURL\Curl;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Ivan1986\DebBundle\Form\PpaRepositoryType;
use Symfony\Component\Validator\Constraints as Assert;
use Ivan1986\DebBundle\Model\DistList;

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
     * @Assert\True(message = "Такого репозитория нет на launchpad.net")
     * @return bool
     */
    public function isExistRepo()
    {
        if ($this->getPpaUrl() == '')
            return false;
        $c = new Curl();
        $c->setURL($this->getPpaUrl());
        $c->execute();
        $info = $c->getInfo();
        //репозиторий существует, заодно получим ключ
        if ($info['http_code'] != 200)
            return false;
        return $this->getKeyFromLaunchpad();
    }

    private function getKeyFromLaunchpad()
    {
        $c = new Curl();
        $c->setOptions(array(
            'CURLOPT_SSL_VERIFYPEER' => false,
        ));
        $c->setURL($this->getPpaPage());
        $data = $c->execute();
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
        return new PpaRepositoryType();
    }

    /**
     * @var Ivan1986\DebBundle\Model\DistList $distrs Список дистрибутивов, соответствующих
     *
     * @ORM\Column(name="distrs", type="object")
     */
    protected $distrs;

    /**
     * @param Ivan1986\DebBundle\Model\DistList $distrs
     */
    public function setDistrs($distrs)
    {
        $this->distrs = $distrs;
    }

    /**
     * @return Ivan1986\DebBundle\Model\DistList
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
