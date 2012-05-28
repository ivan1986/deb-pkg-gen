<?php

namespace Ivan1986\DebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ivan1986\DebBundle\Entity\Package
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ivan1986\DebBundle\Entity\PackageRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="pkgType", type="string")
 * @ORM\DiscriminatorMap({"small" = "Package"})
 */
class Package
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
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

    /**
     * @var string Информация для списка
     *
     * @ORM\Column(name="info", type="text")
     */
    protected $info;

    /**
     * @var Repository Репозиторий этого пакета
     *
     * @ORM\ManyToOne(targetEntity="Ivan1986\DebBundle\Entity\Repository", cascade="all")
     */
    protected $repository;

    /**
     * @var DateTime Время создания пакета
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var string Имя файла
     *
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
        return $this->content;
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
}