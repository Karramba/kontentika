<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Comment controller.
 *
 * @Route("/c")
 */
class CommentController extends Controller
{
    /**
     * Lists all Comment entities.
     *
     * @Route("/", name="comment_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository('AppBundle:Comment')->findAll();

        return $this->render('comment/index.html.twig', array(
            'comments' => $comments,
        ));
    }

    /**
     * Creates a new Comment entity.
     *
     * @Route("/new/{linkUniqueId}", name="comment_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction(Request $request, $linkUniqueId)
    {
        $em = $this->getDoctrine()->getManager();

        $link = $em->getRepository("AppBundle:Link")->findOneByUniqueId($linkUniqueId);
        if (!$link) {
            throw $this->createNotFoundException();
        }

        $comment = new Comment();
        $form = $this->createForm('AppBundle\Form\CommentType', $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $link->addComment($comment);
            $em->persist($link);
            $em->flush();

            $this->get('notification.service')->addMentionNotification($comment, "comment_mention");
        } else {
            // var_dump($form->getErrors());exit;
        }

        return $this->redirectToRoute('link_show', array('uniqueId' => $link->getUniqueId(), 'slug' => $link->getSlug()));
    }

    /**
     * Displays a form to edit an existing Comment entity.
     *
     * @Route("/{uniqueId}/edit", name="comment_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, Comment $comment)
    {
        $editForm = $this->createForm('AppBundle\Form\CommentType', $comment, array('rows' => 20));
        $editForm->handleRequest($request);

        if ($comment->getUser() != $this->getUser()) {
            throw $this->createNotFoundException();
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            // $this->get('notification.service')->addMentionNotification($comment, "comment_edited");
            // $this->addFlash('success', 'comment.edited_and_saved');

            $link = $comment->getLink();
            return $this->redirectToRoute('link_show', array(
                'uniqueId' => $link->getUniqueId(),
                'slug' => $link->getSlug(),
            ));
        }

        return $this->render('comment/edit.html.twig', array(
            'comment' => $comment,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Comment entity.
     *
     * @Route("/{uniqueId}/delete", name="comment_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction(Request $request, Comment $comment)
    {
        if ($comment->getUser() != $this->getUser() &&
            !$this->get('auth.service')->haveModerationToolsAccess($comment->getLink()->getGroup())) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();

        if ($comment->getUser() != $this->getUser()) {
            $this->get('notification.service')->addDeleteNotification($comment);
        }

        $link = $comment->getLink();

        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', 'Komentarz został usunięty');

        return $this->redirectToRoute('link_show', array(
            'uniqueId' => $link->getUniqueId(),
            'slug' => $link->getSlug(),
        ));
    }

    /**
     * Deletes a Comment entity.
     *
     * @Route("/{uniqueId}/reply", name="comment_reply")
     * @Security("has_role('ROLE_USER')")
     */
    public function replyFormAction(Request $request, Comment $comment)
    {
        $reply = new Comment();
        $reply->setContent("@" . $comment->getUser());
        $reply->setUser($this->getUser());

        $form = $this->createForm('AppBundle\Form\CommentType', $reply,
            array(
                'action' => $this->generateUrl('comment_reply', array('uniqueId' => $comment->getUniqueId())),
            )
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $filter = $em->getFilters()->disable('softdeleteable');
            try {
                if ($comment->getFirstParent() instanceof Comment) {
                    $reply->setParent($comment->getFirstParent());
                } else {
                    $reply->setParent($comment);
                }

                $em->persist($reply);
                $em->flush();

                if ($comment->getUser() != $this->getUser()) {
                    $this->get('notification.service')->addReplyNotification($comment);
                }
                $this->get('notification.service')->addMentionNotification($reply);

                $this->addFlash('success', 'comment.added_successfully');
            } catch (\Exception $e) {
                var_dump($e->getMessage());exit;
                $this->addFlash('danger', 'comment.error_adding');
            }

            return $this->redirectToRoute('link_show', array(
                'uniqueId' => $reply->getLink()->getUniqueId(),
                'slug' => $reply->getLink()->getSlug(),
            ));
        }

        return $this->render('comment/reply.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
