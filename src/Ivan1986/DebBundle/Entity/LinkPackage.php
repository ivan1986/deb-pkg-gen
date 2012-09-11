<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ivan1986\DebBundle\Entity\LinkPackage
 *
 * @ORM\Table("Package")
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Entity\PackageRepository")
 */
class LinkPackage extends Package
{
    const NOT_CHECKED = 0;
    const CHECK_NOW   = 1;
    const CHECK_YES   = 2;
    const CHECK_ERR   = 3;

    /**
     * @var User Репозиторий этого пакета
     *
     * @ORM\OneToOne(targetEntity="Ivan1986\DebBundle\Entity\User")
     */
    protected $user;

    /**
     * @var boolean INFO проверен
     *
     * @ORM\Column(name="checked", type="integer")
     */
    protected $checked = self::NOT_CHECKED;

    /**
     * @var string Адрес пакета
     *
     * @Assert\Url(protocols={"http", "https", "ftp"})
     * @ORM\Column(name="link", type="string")
     */
    protected $link = '';

    public function __construct()
    {
        $this->content = "link";
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checked;
    }

    public function getStatus()
    {
        if ($this->checked == self::NOT_CHECKED)
            return "Wait";
        if ($this->checked == self::CHECK_NOW)
            return "Check now";
        if ($this->checked == self::CHECK_YES)
            return "Ok";
        if ($this->checked == self::CHECK_ERR)
            return "Error";
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    public function getHttpResponse()
    {
        return new RedirectResponse($this->link);
    }

    public function getContent()
    {
        return '';
    }

    public function setInfo($info)
    {
        if ($this->info != $info)
            $this->checked = self::NOT_CHECKED;
        //Получаем имя для файла пакета
        $info = str_replace("\r\n", "\n", $info);
        $strs = explode("\n", $info);
        $name = '';
        $pkg = 'Package:';
        $arc = 'Architecture:';
        foreach($strs as $str)
        {
            if (strpos($str, $pkg) !== false)
                $name.= trim(substr($str, strlen($pkg)));
            if (strpos($str, $arc) !== false)
                $name.= '-'.trim(substr($str, strlen($arc)));
        }
        if ($name)
            $name.='.deb';
        $this->setFile($name);
        $fileinfo = "Filename: %filename%\n";
        $info = explode("\n", $info);
        foreach($info as $k=>$v)
            if (strpos($v, "Filename") === 0)
                unset($info[$k]);
        $info = implode("\n", $info);
        $info = str_replace("Description:", $fileinfo."Description:", $info);
        return parent::setInfo($info);
    }

    public function getUserName()
    {
        return $this->getUser() ? $this->getUser()->getUsernameCanonical() : "";
    }

}