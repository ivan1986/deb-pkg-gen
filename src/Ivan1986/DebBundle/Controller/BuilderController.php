<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Ivan1986\DebBundle\Util\Builder;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Ivan1986\DebBundle\Entity\RepositoryRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Entity\Package;

class BuilderController extends Controller
{
    /** @var string Путь до пакетов */
    private $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__).'/package';
    }

    /**
     * @Route("/pkg/", name="pkg_build")
     * @Template()
     */
    public function indexAction()
    {
        if ($this->getRequest()->getMethod() == "POST")
        {
            $form = $this->getRequest()->get('form');
            $error = array(
                'form' => $form,
            );
            try {
                $keys = $this->getDoctrine()->getRepository('Ivan1986DebBundle:GpgKey');
                /** @var $keys GpgKeyRepository */
                $key = $keys->getFromServer($form['key'], $this->container->getParameter('key_server'));

                $repo = new Repository();
                $repo->setRepoString($form['url']);
                $repo->setKey($key);
                $repo->setName($form['name']);
            }
            catch (\Exception $e)
            {
                $error['e'] = $e;
                return $error;
            }

            $builder = new Builder($this->get('templating'));
            $pkg = $builder->simplePackage($repo);

            return $pkg->getHttpResponse();
        }
        return array();
    }

}
