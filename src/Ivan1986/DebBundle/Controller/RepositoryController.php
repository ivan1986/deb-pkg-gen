<?php

namespace Ivan1986\DebBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Ivan1986\DebBundle\Entity\PpaRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Repository controller.
 *
 * @Route("/repos")
 */
class RepositoryController extends Controller
{
    /** @var ObjectManager */
    private $em;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->em = $this->getDoctrine()->getManager();
    }

    /**
     * Lists all Repository entities.
     *
     * @Route("/{my}/{page}", name="repos", requirements={"my" = "my|all", "page" = "\d+"},
     *  defaults={"page" = 1, "my"="my"})
     * @Template()
     */
    public function indexAction($my, $page, Request $r)
    {
        $search = $r->query->get('search');
        $query = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getByUser(($my == 'my' && !$search) ? $this->getUser() : null)
            ->leftJoin('r.packages', 'p')
            ->select('r, p')
        ;
        /* @var $query QueryBuilder */
        if ($search) {
            $query->andWhere($query->expr()->orX(
                $query->expr()->like('r.name', '?1'),
                $query->expr()->like('r.repoString', '?1')))->setParameter(1, '%'.$search.'%');
        }

        $adapter = new DoctrineORMAdapter($query);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        try {
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'all' => $my != 'my',
            'router' => $this->get('router'),
            'pagerfanta' => $pagerfanta,
        ];
    }

    /**
     * Displays a form to create a new Repository entity.
     *
     * @Route("/new", name="repos_new")
     * @Template()
     */
    public function newAction(Request $r)
    {
        $entity = new Repository();
        $entity->setContainer($this->container);
        $form = $this->createForm($entity->getFormClass(), $entity);

        $form->handleRequest($r);
        if ($form->isValid()) {
            $entity->setOwner($this->getUser());
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirect($this->generateUrl('repos'));
        }

        return [
            'to' => 'repos_new',
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new Repository entity.
     *
     * @Route("/new_ppa", name="repos_new_ppa")
     * @Template("Ivan1986DebBundle:Repository:new.html.twig")
     */
    public function newPpaAction(Request $r)
    {
        $entity = new PpaRepository();
        $entity->setContainer($this->container);
        $form = $this->createForm($entity->getFormClass(), $entity);

        $form->handleRequest($r);
        if ($form->isValid()) {
            $entity->setOwner($this->getUser());
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirect($this->generateUrl('repos'));
        }

        return [
            'to' => 'repos_new_ppa',
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Repository entity.
     *
     * @Route("/{id}/edit", name="repos_edit")
     * @Template()
     */
    public function editAction($id, Request $r)
    {
        $entity = $this->getByID($id);
        $entity->setContainer($this->container);
        $editForm = $this->createForm($entity->getFormClass(), $entity);

        $editForm->handleRequest($r);
        if ($editForm->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirect($this->generateUrl('repos'));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a Repository entity.
     *
     * @Route("/{id}/delete", name="repos_delete")
     * @Method("get")
     */
    public function deleteAction($id)
    {
        $entity = $this->getByID($id);
        /* @var Repository $entity */
        $entity->setContainer($this->container);
        //удаляем пакеты этого репозитория
        foreach ($entity->getPackages() as $pkg) {
            $this->em->remove($pkg);
        }
        $this->em->remove($entity);
        $this->em->flush();

        return $this->redirect($this->generateUrl('repos'));
    }

    /**
     * Получаем репозиторий по ID с проверкой пользователя.
     *
     * @param $id
     *
     * @throws NotFoundHttpException
     *
     * @return Repository
     */
    private function getByID($id)
    {
        $entity = $this->em->getRepository('Ivan1986DebBundle:Repository')
            ->getByIdAndCheckUser($id, $this->getUser());
        /** @var Repository $entity */
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repository entity.');
        }

        return $entity;
    }
}
