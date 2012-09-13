<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ivan1986\DebBundle\Entity\LinkPackage;
use Ivan1986\DebBundle\Entity\PpaRepository;
use Ivan1986\DebBundle\Form\RepositoryType;
use Ivan1986\DebBundle\Form\LinkPackageType;
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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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

    /**
     * @param QueryBuilder $query
     */
    private function addRepoSearch(QueryBuilder $query)
    {
        $search = $this->getRequest()->query->get('search');
        if ($search)
            $query->andWhere($query->expr()->orX(
                    $query->expr()->like('r.name', '?1'),
                    $query->expr()->like('r.repoString', '?1')))->setParameter(1, '%'.$search.'%');
    }

    /**
     * @ApiDoc(resource=true, description="Return count repos",
     * filters={
     *      {"name"="search", "dataType"="string"}
     *  }
     * )
     */
    public function getReposCountAction()
    {
        $query = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getAllQB();
        /** @var $query QueryBuilder */
        $this->addRepoSearch($query);
        $count = $query->select($query->expr()->count('r'))->getQuery()->getSingleScalarResult();
        $view = $this->view(array('count' => $count), 200);
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(resource=true, description="Return list of repository",
     * filters={
     *      {"name"="search", "dataType"="string"},
     *      {"name"="from", "dataType"="integer"},
     *      {"name"="count", "dataType"="integer"}
     *  }
     * )
     */
    public function getReposListAction()
    {
        $search = $this->getRequest()->query->get('search');
        $query = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getAllQB();
        /** @var $query QueryBuilder */
        $this->addRepoSearch($query);
        $query->join('r.key', 'k');
        $query->select('r, k');
        $query->setFirstResult($this->getRequest()->query->get('from', 0));
        $query->setMaxResults($this->getRequest()->query->get('count', 10));
        $result = $query->getQuery()->getResult();
        $view = $this->view($result, 200);
        return $this->handleView($view);
    }


    /**
     * @ApiDoc(resource=true, description="Create new Standart Repository")
     */
    public function postReposNewStdAction(Request $request)
    {
        return $this->processForm(new Repository());
    }

    /**
     * @ApiDoc(resource=true, description="Create new PPA Repository")
     */
    public function postReposNewPpaAction(Request $request)
    {
        return $this->processForm(new PpaRepository());
    }

    /**
     * @ApiDoc(resource=true, description="Edit Repository")
     */
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

        if ($form->isValid()) {
            $repo->setOwner($this->getUser());
            $this->em->persist($repo);
            $this->em->flush();

            return $this->handleView($this->view(array('id' => $repo->getId()), $statusCode));
        }

        return $this->handleView($this->view(array($form->getName()=>$form), 400));
    }

    /**
     * @ApiDoc(resource=true, description="Delete Repository",
     *     input="\Ivan1986\DebBundle\Entity\Repository"
     * )
     */
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

    /**
     * @param QueryBuilder $query
     */
    private function addPkgSearch(QueryBuilder $query)
    {
        $search = $this->getRequest()->query->get('search');
        if ($search)
            $query->andWhere($query->expr()->orX(
                    $query->expr()->like('p.file', '?1'),
                    $query->expr()->like('p.link', '?1')))->setParameter(1, '%'.$search.'%');
    }

    /**
     * @ApiDoc(resource=true, description="Return count packages",
     * filters={
     *      {"name"="search", "dataType"="string"}
     *  }
     * )
     */
    public function getPkgsCountAction()
    {
        $query = $this->em->getRepository('Ivan1986DebBundle:LinkPackage')
            ->getAllQB();
        /** @var $query QueryBuilder */
        $this->addPkgSearch($query);
        $count = $query->select($query->expr()->count('p'))->getQuery()->getSingleScalarResult();
        $view = $this->view(array('count' => $count), 200);
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(resource=true, description="Return list of packages",
     * filters={
     *      {"name"="search", "dataType"="string"},
     *      {"name"="from", "dataType"="integer"},
     *      {"name"="count", "dataType"="integer"}
     *  }
     * )
     */
    public function getPkgsListAction()
    {
        $search = $this->getRequest()->query->get('search');
        $query = $this->em->getRepository('Ivan1986DebBundle:LinkPackage')
            ->getAllQB();
        /** @var $query QueryBuilder */
        $this->addPkgSearch($query);
        $query->setFirstResult($this->getRequest()->query->get('from', 0));
        $query->setMaxResults($this->getRequest()->query->get('count', 10));
        $result = $query->getQuery()->getResult();
        $view = $this->view($result, 200);
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(resource=true, description="Create new Package")
     */
    public function postPkgsNewAction(Request $request)
    {
        return $this->processPkgForm(new LinkPackage());
    }

    /**
     * @ApiDoc(resource=true, description="Edit Package")
     */
    public function putPkgAction(LinkPackage $pkg)
    {
        return $this->processPkgForm($pkg);
    }

    private function processPkgForm(LinkPackage $pkg)
    {
        if (!$pkg)
            throw new NotFoundHttpException();
        $statusCode = !$pkg->getId() ? 201 : 204;

        $form = $this->createForm(new LinkPackageType(), $pkg, array( 'csrf_protection'   => false, ));
        $form->bind($this->getRequest());

        if ($form->isValid()) {
            $pkg->setOwner($this->getUser());
            $this->em->persist($pkg);
            $this->em->flush();

            return $this->handleView($this->view(array('id' => $pkg->getId()), $statusCode));
        }

        return $this->handleView($this->view(array($form->getName()=>$form), 400));
    }

    /**
     * @ApiDoc(resource=true, description="Delete Package",
     *     input="\Ivan1986\DebBundle\Entity\Repository"
     * )
     */
    public function deletePkgAction(LinkPackage $pkg)
    {
        if (!$pkg)
            throw new NotFoundHttpException();
        if ($pkg->getOwner() != $this->getUser())
            throw new AccessDeniedException();
        /** @var Repository $entity */
        $pkg->setContainer($this->container);
        //удаляем пакеты этого репозитория
        foreach($pkg->getPackages() as $pkg)
            $this->em->remove($pkg);
        $this->em->remove($pkg);
        $this->em->flush();
        return $this->handleView($this->view(null, 204));
    }

}