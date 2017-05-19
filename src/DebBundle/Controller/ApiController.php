<?php

namespace Ivan1986\DebBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\FOSRestController;
use Ivan1986\DebBundle\Entity\PpaRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * @Prefix("api")
 * @NamePrefix("api_")
 */
class ApiController extends FOSRestController
{
    /** @var ObjectManager */
    protected $em;

    /** @var Analytics */
    private $analytics;

    /**
     * RepositoryController constructor.
     * @param EntityManager $em
     * @param Analytics $analytics
     */
    public function __construct(EntityManager $em, Analytics $analytics)
    {
        $this->em = $em;
        $this->analytics = $analytics;
        $this->analytics->setEventCategory('API');
    }

    /**
     * @param QueryBuilder $query
     */
    private function addRepoSearch(Request $r, QueryBuilder $query)
    {
        $search = $r->query->get('search');
        if ($search) {
            $query->andWhere($query->expr()->orX(
                    $query->expr()->like('r.name', '?1'),
                    $query->expr()->like('r.repoString', '?1')))->setParameter(1, '%'.$search.'%');
        }
    }

    /**
     * @ApiDoc(resource=true, description="Return count repos",
     * filters={
     *      {"name"="search", "dataType"="string"}
     *  }
     * )
     */
    public function getReposCountAction(Request $r)
    {
        $query = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getAllQB();
        /* @var $query QueryBuilder */
        $this->addRepoSearch($r, $query);
        $count = $query->select($query->expr()->count('r'))->getQuery()->getSingleScalarResult();
        $view = $this->view(['count' => $count], 200);

        $this->analytics->setEventAction('Count')->sendEvent();

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
    public function getReposListAction(Request $r)
    {
        $query = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getAllQB();
        /* @var $query QueryBuilder */
        $this->addRepoSearch($r, $query);
        $query->join('r.key', 'k');
        $query->select('r, k');
        $query->setFirstResult($r->query->get('from', 0));
        $query->setMaxResults($r->query->get('count', 10));
        $result = $query->getQuery()->getResult();
        $view = $this->view($result, 200);

        $this->analytics->setEventAction('List')->sendEvent();

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(resource=true, description="Create new Standart Repository")
     */
    public function postReposNewStdAction(Request $r)
    {
        $this->analytics->setEventAction('Create')->sendEvent();

        return $this->processForm($r, new Repository());
    }

    /**
     * @ApiDoc(resource=true, description="Create new PPA Repository")
     */
    public function postReposNewPpaAction(Request $r)
    {
        $this->analytics->setEventAction('Create PPA')->sendEvent();

        return $this->processForm($r, new PpaRepository());
    }

    /**
     * @ApiDoc(resource=true, description="Edit Repository")
     */
    public function putRepoAction(Request $r, Repository $repo)
    {
        $this->analytics->setEventAction('Update')->sendEvent();

        return $this->processForm($r, $repo);
    }

    private function processForm(Request $r, Repository $repo)
    {
        if (!$repo) {
            throw new NotFoundHttpException();
        }
        $statusCode = !$repo->getId() ? 201 : 204;

        $form = $this->createForm($repo->getFormClass(), $repo, ['csrf_protection' => false]);
        $form->handleRequest($r);

        if ($form->isValid()) {
            $repo->setOwner($this->getUser());
            $this->em->persist($repo);
            $this->em->flush();

            return $this->handleView($this->view(['id' => $repo->getId()], $statusCode));
        }

        return $this->handleView($this->view([$form->getName() => $form], 400));
    }

    /**
     * @ApiDoc(resource=true, description="Delete Repository",
     *     input="\Ivan1986\DebBundle\Entity\Repository"
     * )
     */
    public function deleteRepoAction(Repository $repo)
    {
        if (!$repo) {
            throw new NotFoundHttpException();
        }
        if ($repo->getOwner() != $this->getUser()) {
            throw new AccessDeniedException();
        }
        /* @var Repository $entity */
        //удаляем пакеты этого репозитория
        foreach ($repo->getPackages() as $pkg) {
            $this->em->remove($pkg);
        }
        $this->em->remove($repo);
        $this->em->flush();

        $this->analytics->setEventAction('Delete')->sendEvent();

        return $this->handleView($this->view(null, 204));
    }
}
