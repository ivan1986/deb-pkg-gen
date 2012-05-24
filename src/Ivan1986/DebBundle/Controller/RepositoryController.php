<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Form\RepositoryType;

/**
 * Repository controller.
 *
 * @Route("/profile/repos")
 */
class RepositoryController extends Controller
{
    /**
     * Lists all Repository entities.
     *
     * @Route("/", name="repos")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('Ivan1986DebBundle:Repository')->getByUser($this->getUser());

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Repository entity.
     *
     * @Route("/{id}/show", name="repos_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('Ivan1986DebBundle:Repository')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repository entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
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

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Repository entity.
     *
     * @Route("/create", name="repos_create")
     * @Method("post")
     * @Template("Ivan1986DebBundle:Repository:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Repository();
        $request = $this->getRequest();
        $form    = $this->createForm(new RepositoryType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('repos_show', array('id' => $entity->getId())));
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
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('Ivan1986DebBundle:Repository')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repository entity.');
        }

        $editForm = $this->createForm(new RepositoryType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Repository entity.
     *
     * @Route("/{id}/update", name="repos_update")
     * @Method("post")
     * @Template("Ivan1986DebBundle:Repository:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('Ivan1986DebBundle:Repository')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repository entity.');
        }

        $editForm   = $this->createForm(new RepositoryType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('repos_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Repository entity.
     *
     * @Route("/{id}/delete", name="repos_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('Ivan1986DebBundle:Repository')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Repository entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('repos'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
