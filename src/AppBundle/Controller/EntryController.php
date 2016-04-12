<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Entry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * Entry controller.
 *
 * @Route("/e")
 */
class EntryController extends Controller
{
    /**
     * Lists all Entry entities.
     *
     * @Route("/", name="entry_index")
     * @Route("/p/{page}", name="entry_page")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $filter = $em->getFilters()->disable('softdeleteable');

        $result = $em->getRepository('AppBundle:Entry')->findEntries($page, $this->getParameter('content_per_page'));

        $entry = new Entry();
        $form = $this->createForm('AppBundle\Form\EntryType', $entry, array('em' => $em));
        $form->handleRequest($request);

        if ($this->getUser() instanceof User && $form->isSubmitted() && $form->isValid()) {
            $this->getUser()->addEntry($entry);

            $em->persist($entry);
            $em->flush();

            $this->get('notification.service')->addMentionNotification($entry, "entry_mention");

            return $this->redirectToRoute('entry_index');
        }

        return $this->render('entry/index.html.twig', array(
            'entries' => $result['entries'],
            'entriesNumber' => $result['entriesNumber'],
            'form' => $form->createView(),
            'page' => $page,
            'pages' => ceil($result['entriesNumber'] / $this->getParameter('content_per_page')),
            'paginationRoute' => 'entry_page',
        ));
    }
    /**
     * Displays a form to edit an existing Entry entity.
     *
     * @Route("/{uniqueId}", name="entry_show")
     * @Method({"GET", "POST"})
     */
    public function showAction(Request $request, $uniqueId)
    {
        $em = $this->getDoctrine()->getManager();
        $filter = $em->getFilters()->disable('softdeleteable');
        $entry = $em->getRepository("AppBundle:Entry")->findEntry($uniqueId);

        if (!$entry) {
            throw $this->createNotFoundException();
        }

        return $this->render('entry/show.html.twig', array(
            'entry' => $entry,
        ));
    }

    /**
     * Displays a form to edit an existing Entry entity.
     *
     * @Route("/{uniqueId}/edit", name="entry_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, Entry $entry)
    {
        if ($entry->getUser() != $this->getUser()) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $editForm = $this->createForm('AppBundle\Form\EntryType', $entry, array('em' => $em));
        $editForm->remove('group');
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            $this->addFlash('success', 'entry.edited_and_saved');

            $this->get('notification.service')->addMentionNotification($entry, "entry_edited");

            if ($entry->getParent()) {
                $uniqueId = $entry->getParent()->getUniqueId();
            } else {
                $uniqueId = $entry->getUniqueId();
            }

            return $this->redirectToRoute('entry_show', array('uniqueId' => $uniqueId));
        }

        return $this->render('entry/edit.html.twig', array(
            'entry' => $entry,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Entry entity.
     *
     * @Route("/{uniqueId}/delete", name="entry_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction(Request $request, Entry $entry)
    {
        $em = $this->getDoctrine()->getManager();
        $filter = $em->getFilters()->disable('softdeleteable');

        if ($entry->getUser() != $this->getUser() &&
            !$this->get('auth.service')->haveModerationToolsAccess($entry->getGroup())) {
            throw $this->createNotFoundException();
        }

        if ($entry->getUser() != $this->getUser()) {
            $this->get('notification.service')->addReplyNotification($entry, "entry_deleted");
        }

        $em->remove($entry);
        $em->flush();

        $this->addFlash('success', 'entry.deleted');

        return $this->redirectToRoute('entry_index');
    }

    /**
     * Deletes a Entry entity.
     *
     * @Route("/{uniqueId}/reply", name="entry_reply")
     * @Security("has_role('ROLE_USER')")
     */
    public function replyFormAction(Request $request, Entry $entry)
    {
        $em = $this->getDoctrine()->getManager();

        $filter = $em->getFilters()->disable('softdeleteable');

        $reply = new Entry();
        $reply->setContent("@" . $entry->getUser());
        $reply->setUser($this->getUser());

        $form = $this->createForm('AppBundle\Form\EntryReplyType', $reply,
            array(
                'action' => $this->generateUrl('entry_reply', array('uniqueId' => $entry->getUniqueId())),
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($entry->getFirstParent() instanceof Entry) {
                    $parent = $entry->getFirstParent();
                } else {
                    $parent = $entry;
                }

                if ($entry->getDeletedAt() == null) {
                    $reply->setParent($parent);
                    $em->persist($reply);
                    $em->flush();
                    $this->addFlash('success', 'entry.added_successfully');

                    if ($entry->getUser() != $this->getUser()) {
                        $this->get('notification.service')->addReplyNotification($entry, "entry_replied");
                    }
                    $this->get('notification.service')->addMentionNotification($reply, "entry_mention");

                } else {
                    $this->addFlash('danger', 'entry.cannot_add_entry_deleted');
                }

            } catch (\Exception $e) {
                $this->addFlash('danger', 'entry.error_adding');
            }

            $route = $this->generateUrl('entry_show', array(
                'uniqueId' => $parent->getUniqueId(),
            ));
            return $this->redirect($route . "#" . $reply->getUniqueId());
        }

        return $this->render('entry/reply.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
