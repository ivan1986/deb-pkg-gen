<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Anchovy\CURLBundle\CURL\Curl;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Ivan1986\DebBundle\Form\PpaRepositoryType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ivan1986\DebBundle\Entity\PpaRepository
 *
 * @ORM\Entity()
 */
class PpaRepository extends Repository
{
    public function setRepoString($string)
    {
        if (strpos($string, 'ppa:') !== 0)
            $string = 'ppa:'.$string;
        if (strpos($string, '/') === false)
            $string = $string.'/ppa';
        $this->setName(str_replace(array(':', '/'), '-', $string));
        return parent::setRepoString($string);
    }

    public function getDebStrings()
    {
        return parent::getDebStrings();
    }

    protected function isValid()
    {
        if (strpos($this->repoString, 'ppa:') !== 0)
            return false;
        if (strpos($this->repoString, '/', strpos($this->repoString, '/')+1) !== false)
            return false;
        return true;
    }

    /**
     * Проверяет валидность строки репозитория
     *
     * @Assert\True(message = "Такого репозитория нет на launchpad.net")
     * @return bool
     */
    public function isExistRepo()
    {
        $str = substr($this->repoString, 4);
        $str = explode('/', $str);
        if (count($str) != 2)
            return false;
        $c = new Curl();
        $c->setURL('http://ppa.launchpad.net/'.$str[0].'/'.$str[1].'/ubuntu/');
        $c->execute();
        $info = $c->getInfo();
        //репозиторий существует, заодно получим ключ
        $this->getKeyFromLaunchpad();
        return $info['http_code'] == 200;
    }

    private function getKeyFromLaunchpad()
    {
    }

    public function getFormClass()
    {
        return new PpaRepositoryType();
    }

}
