<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Form\RepositoryType;
use Ivan1986\DebBundle\Exception\ParseRepoStringException;

/**
 * Repository controller.
 *
 * @Route("/profile/repos")
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
     * @Route("/", name="repos")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->em->getRepository('Ivan1986DebBundle:Repository')->getByUser($this->getUser());

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Displays a form to create a new Repository entity.
     *
     * @Route("/new", name="repos_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Repository();
        $form   = $this->createForm(new RepositoryType(), $entity);

        if ($this->getRequest()->getMethod() == 'POST')
        {
            try
            {
                $form->bindRequest($this->getRequest());
                if ($form->isValid()) {
                    $entity->setOwner($this->getUser());
                    $this->em->persist($entity);
                    $this->em->flush();

                    return $this->redirect($this->generateUrl('repos'));
                }
            }
            catch(ParseRepoStringException $e)
            {
                $form->addError(new FormError($e->__toString()));
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Repository entity.
     *
     * @Route("/{id}/edit", name="repos_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $entity = $this->getByID($id);
        $editForm = $this->createForm(new RepositoryType(), $entity);

        if ($this->getRequest()->getMethod() == 'POST')
        {
            try
            {
                $editForm->bindRequest($this->getRequest());
                if ($editForm->isValid()) {
                    $this->em->persist($entity);
                    $this->em->flush();

                    return $this->redirect($this->generateUrl('repos'));
                }
            }
            catch(ParseRepoStringException $e)
            {
                $editForm->addError(new FormError($e->__toString()));
            }
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
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
        /** @var Repository $entity */

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repository entity.');
        }
        //удаляем пакеты этого репозитория
        foreach($entity->getPackages() as $pkg)
            $this->em->remove($pkg);
        $this->em->remove($entity);
        $this->em->flush();

        return $this->redirect($this->generateUrl('repos'));
    }

    /**
     * Получаем репозиторий по ID с проверкой пользователя
     *
     * @param $id
     * @return Repository
     * @throws NotFoundHttpException
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
