<?php

namespace Ivan1986\DebBundle\Controller;

use Doctrine\ORM\EntityManager;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Repository controller.
 *
 * @Route("/repos")
 */
class RepositoryController extends Controller
{
    /** @var EntityManager */
    private $em;

    /**
     * RepositoryController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Lists all Repository entities.
     *
     * @Route("/{my}/{page}", name="repos", requirements={"my" = "my|all", "page" = "\d+"},
     *  defaults={"page" = 1, "my"="my"})
     * @Method("GET")
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
            'my' => $my,
            'header' => $this->get('translator')->trans(
                $my ? 'Список добавленных вами репозиториев' : 'Список всех репозиториев'
            ),
            'title' => $this->get('translator')->trans(
                $my ? 'Список добавленных вами репозиториев' : 'Список репозиториев в системе'
            ),
            'switch' => $my == 'all' ? 'my' : 'all',
            'switchName' => $this->get('translator')->trans(
                $my ? 'все' : 'ваши'
            ),
            'pagerfanta' => $pagerfanta,
        ];
    }

    /**
     * Displays a form to create a new Repository entity.
     *
     * @Route("/new", name="repos_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $r)
    {
        $entity = new Repository();
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
     * @Method({"GET", "POST"})
     * @Template("Ivan1986DebBundle:Repository:new.html.twig")
     */
    public function newPpaAction(Request $r)
    {
        $entity = new PpaRepository();
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
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction($id, Request $r)
    {
        $entity = $this->getByID($id);
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
     * @Method("POST")
     */
    public function deleteAction($id, Request $r)
    {
        $entity = $this->getByID($id);
        /* @var Repository $entity */

        if ($entity->getName() != $r->get('name')) {
            return $this->redirect($this->generateUrl('repos_edit', ['id' => $id]));
        }

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
