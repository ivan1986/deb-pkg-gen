<?php

namespace Ivan1986\DebBundle\Controller;

use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Model\GpgLoader;
use Ivan1986\DebBundle\Util\Builder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BuilderController extends Controller
{
    /**
     * @Route("/pkg/", name="pkg_build")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function indexAction(Request $r)
    {
        if ($r->getMethod() == 'POST') {
            $form = $r->get('form');
            $error = [
                'form' => $form,
            ];
            try {
                $key = GpgLoader::getFromServer($form['key'], $this->container->getParameter('key_server'));

                $repo = new Repository();
                $repo->setRepoString($form['url']);
                $repo->setKey($key);
                $repo->setName($form['name']);
            } catch (\Exception $e) {
                $error['e'] = $this->get('translator')->trans('Ключ с ID %key% не найден на сервере', ['%key%' => $e]);

                return $error;
            }

            $builder = new Builder($this->get('templating'));
            $pkg = $builder->simplePackage($repo);

            return $pkg->getHttpResponse();
        }

        return [];
    }
}