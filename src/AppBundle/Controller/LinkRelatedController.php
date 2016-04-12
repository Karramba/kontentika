<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Link;
use AppBundle\Entity\LinkRelated;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/lr")
 */
class LinkRelatedController extends Controller
{
    /**
     * Creates a new Link entity.
     *
     * @Route("/new/{uniqueId}", name="link_new_related")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function newRelatedAction(Request $request, Link $link)
    {
        $em = $this->getDoctrine()->getManager();

        $linkRelated = new LinkRelated();
        $form = $this->createForm('AppBundle\Form\LinkRelatedType', $linkRelated, array('em' => $em));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $domain = $em->getRepository("AppBundle:Domain")->findDomain($linkRelated->getUrl());
            // $linkRelated->setDomain($domain);
            // $linkRelated->setImageOnly($this->get('link.service')->isImageOnly($linkRelated->getUrl()));

            $this->get('link.service')->downloadAndSaveThumbnail($linkRelated->getThumbnail());
            $link->addRelated($linkRelated);
            $linkRelated->setUser($this->getUser());

            $em->persist($linkRelated);
            $em->flush();

            $this->addFlash('success', 'link.added_flash');

            return $this->redirectToRoute('link_show', array('slug' => $link->getSlug(), 'uniqueId' => $link->getUniqueId()));
        }

        return $this->render('linkrelated/new.html.twig', array(
            'link' => $link,
            'form' => $form->createView(),
        ));
    }

    /**
     * Deletes a Link entity.
     *
     * @Route("/d/{uniqueId}", name="linkRelated_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction(Request $request, LinkRelated $linkRelated)
    {
        $link = $linkRelated->getLink();

        if ($linkRelated->getUser() != $this->getUser() &&
            !$this->get('auth.service')->haveModerationToolsAccess($linkRelated->getLink()->getGroup())) {
            $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();

        if ($linkRelated->getUser() != $this->getUser()) {
            $this->get('notification.service')->addReplyNotification($link, "link_deleted");
        }

        $em->remove($linkRelated);
        $em->flush();

        $this->addFlash('success', 'link.deleted');

        return $this->redirectToRoute('link_show', array('slug' => $link->getSlug(), 'uniqueId' => $link->getUniqueId()));
    }

}
