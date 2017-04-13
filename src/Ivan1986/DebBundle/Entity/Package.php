<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ivan1986\DebBundle\Entity\Package
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Repository\PackageRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="pkgType", type="string")
 * @ORM\DiscriminatorMap({"simple" = "SimplePackage", "sys" = "SysPackage"})
 * @UniqueEntity({"fields"="file", "message"="Файл с таким именем уже есть в системе"})
 */
abstract class Package
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
     * @var string содержимое
     *
     * @ORM\Column(name="content", type="blob")
     */
    protected $content;

    /**
     * @var string Информация для списка
     *
     * @ORM\Column(name="info", type="text")
     */
    protected $info;

    /**
     * @var \DateTime Время создания пакета
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var string Имя файла
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="file", type="string")
     */
    protected $file;

    public function getHttpResponse()
    {
        $headers = array(
            'Content-Type'        => 'application/x-debian-package',
            'Content-Disposition' => sprintf('attachment; filename="%s"',
                $this->getFile()),
        );
        return new Response($this->getContent(), 200, $headers);
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Package
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return is_resource($this->content) ? stream_get_contents($this->content) : $this->content;
    }

    /**
     * Set info
     *
     * @param string $info
     * @return Package
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set file
     *
     * @param string $file
     * @return Package
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function getName()
    {
        if (!$this->info)
            return '';
        $strings = explode("\n", $this->info);
        $name = '';
        $pkg = 'Package:';
        foreach($strings as $str)
        {
            if (strpos($str, $pkg) !== false)
            {
                $name = trim(substr($str, strlen($pkg)));
                break;
            }
        }
        return $name;
    }

}
