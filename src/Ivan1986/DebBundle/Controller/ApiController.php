<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ivan1986\DebBundle\Entity\PpaRepository;
use Ivan1986\DebBundle\Form\RepositoryType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ivan1986\DebBundle\Entity\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View AS FOSView,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Request\ParamFetcherInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

/**
 * @Prefix("api")
 * @NamePrefix("api_")
 */
class ApiController extends FOSRestController
{
    /** @var ObjectManager */
    private $em;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->em = $this->getDoctrine()->getManager();
        $this->get('ivan1986_deb.gapinger')->pingGA('API');
    }


    public function getReposCountAction()
    {
        $query = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getAllQB();
        /** @var $query QueryBuilder */
        $count = $query->select($query->expr()->count('r'))->getQuery()->getSingleScalarResult();
        $view = $this->view(array('count' => $count), 200);
        return $this->handleView($view);
    }

    public function getReposListAction()
    {
        $search = $this->getRequest()->query->get('search');
        $query = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getAllQB();
        /** @var $query QueryBuilder */
        if ($search)
            $query->andWhere($query->expr()->orX(
                    $query->expr()->like('r.name', '?1'),
                    $query->expr()->like('r.repoString', '?1')))->setParameter(1, '%'.$search.'%');
        $query->join('r.key', 'k');
        $query->select('r, k');
        $query->setFirstResult($this->getRequest()->query->get('from', 0));
        $query->setMaxResults($this->getRequest()->query->get('count', 10));
        $result = $query->getQuery()->getResult();
        $view = $this->view($result, 200);
        return $this->handleView($view);
    }


    public function postReposNewStdAction(Request $request)
    {
        return $this->processForm(new Repository());
    }

    public function postReposNewPpaAction(Request $request)
    {
        return $this->processForm(new PpaRepository());
    }

    public function putRepoAction(Repository $repo)
    {
        return $this->processForm($repo);
    }

    private function processForm(Repository $repo)
    {
        if (!$repo)
            throw new NotFoundHttpException();
        $statusCode = !$repo->getId() ? 201 : 204;
        $repo->setContainer($this->container);

        $form = $this->createForm($repo->getFormClass(), $repo, array( 'csrf_protection'   => false, ));
        $form->bind($this->getRequest());
        //var_dump($form);

        if ($form->isValid()) {
            $repo->setOwner($this->getUser());
            $this->em->persist($repo);
            $this->em->flush();

            return $this->handleView($this->view(array('id' => $repo->getId()), $statusCode));
        }

        return $this->handleView($this->view(array($form->getName()=>$form), 400));
    }

    public function deleteRepoAction(Repository $repo)
    {
        if (!$repo)
            throw new NotFoundHttpException();
        if ($repo->getOwner() != $this->getUser())
            throw new AccessDeniedException();
        /** @var Repository $entity */
        $repo->setContainer($this->container);
        //удаляем пакеты этого репозитория
        foreach($repo->getPackages() as $pkg)
            $this->em->remove($pkg);
        $this->em->remove($repo);
        $this->em->flush();
        return $this->handleView($this->view(null, 204));
    }

}
