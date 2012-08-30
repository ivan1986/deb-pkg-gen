<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        //TODO: сделать
    }

    public function postReposNewPpaAction(Request $request)
    {
        //TODO: сделать
    }

    public function putRepoAction(Repository $repo)
    {
        //TODO: сделать
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
