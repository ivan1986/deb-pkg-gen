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
        $strs = explode("\n", $info);
        $name = '';
        $pkg = 'Package:';
        $arc = 'Architecture:';
        foreach($strs as $str)
        {
            if (strpos($str, $pkg) !== false)
                $name.= substr($str, strlen($pkg)+1);
            if (strpos($str, $arc) !== false)
                $name.= '-'.substr($str, strlen($arc)+1);
        }
        if ($name)
            $name.='.deb';
        $this->setFile($name);
        $fileinfo = "Filename: %filename%\n";
        $info = str_replace("Description:", $fileinfo."Size:", $info);
        return parent::setInfo($info);
    }

}