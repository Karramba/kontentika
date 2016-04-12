<?php

namespace AppBundle\Controller;

use AppBundle\Entity\LinkGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * LinkGroup controller.
 *
 */
class LinkGroupController extends Controller
{
    /**
     * Lists all LinkGroup entities.
     *
     * @Route("/g", name="linkgroup_index")
     * @Route("/g-{page}", name="linkgroup_index_page", requirements={"page": "[0-9]+"})
     * @Method("GET")
     */
    public function indexAction($page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:LinkGroup')->findAllGroups($page, $this->getParameter('content_per_page'));

        return $this->render('linkgroup/index.html.twig', array(
            'linkGroups' => $result['linkGroups'],
            'page' => $page,
            'pages' => ceil($result['linkGroupsNumber'] / $this->getParameter('content_per_page')),
            'linkGroupsNumber' => $result['linkGroupsNumber'],
        ));
    }

    /**
     * Lists all LinkGroup entities.
     *
     * @Route("/g-my", name="my_linkgroups")
     * @Route("/g-my-{page}", name="my_linkgroups_page", requirements={"page": "[0-9]+"})
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function myGroupsAction($page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:LinkGroup')
            ->findUserGroups($page, $this->getParameter('content_per_page'), $this->getUser());

        return $this->render('linkgroup/index.html.twig', array(
            'linkGroups' => $result['linkGroups'],
            'page' => $page,
            'pages' => ceil($result['linkGroupsNumber'] / $this->getParameter('content_per_page')),
            'linkGroupsNumber' => $result['linkGroupsNumber'],
        ));
    }

    /**
     * Lists all LinkGroup entities.
     *
     * @Route("/g-u/{username}", name="user_linkgroups")
     * @Route("/g-u/{username}-{page}", name="user_linkgroups_page", requirements={"page": "[0-9]+"})
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function userGroupsAction($page = 1, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:LinkGroup')
            ->findUserGroups($page, $this->getParameter('content_per_page'), $user);

        return $this->render('linkgroup/index.html.twig', array(
            'linkGroups' => $result['linkGroups'],
            'page' => $page,
            'pages' => ceil($result['linkGroupsNumber'] / $this->getParameter('content_per_page')),
            'linkGroupsNumber' => $result['linkGroupsNumber'],
            'user' => $user,
        ));
    }

    /**
     * Creates a new LinkGroup entity.
     *
     * @Route("/g-new", name="linkgroup_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $linkGroup = new LinkGroup();
        $form = $this->createForm('AppBundle\Form\LinkGroupType', $linkGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $linkGroup->setOwner($this->getUser());
            $em->persist($linkGroup);
            $em->flush();
            $this->addFlash('success', 'linkgroup.created_flash');

            return $this->redirectToRoute('link_group_show', array('title' => $linkGroup->getTitle()));
        }

        return $this->render('linkgroup/new.html.twig', array(
            'linkGroup' => $linkGroup,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/g-search", name="linkgroup_search")
     * @Method({"POST"})
     */
    public function searchGroupAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $groups = $em->getRepository("AppBundle:LinkGroup")->findForAutocompleter($request->request->get('group'));
        // var_dump(array_map('current', $groups));exit;
        return new JsonResponse(array_map('current', $groups));
    }
    /**
     * Displays a form to edit an existing LinkGroup entity.
     *
     * @Route("/g/{title}/edit", name="linkgroup_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, LinkGroup $linkGroup)
    {
        if (!$this->get('auth.service')->isGroupAdmin($linkGroup)) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\LinkGroupEditType', $linkGroup, array('em' => $em));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            foreach ($linkGroup->getModerators() as $moderator) {
                if ($moderator == $this->getUser()) {
                    $linkGroup->removeModerator($moderator);
                    $this->addFlash('warning', 'linkgroup.groupowner_as_moderator_forbidden');
                }
            }
            $em->persist($linkGroup);
            $em->flush();

            $this->addFlash('success', 'linkgroup.settings_saved');

            return $this->redirectToRoute('linkgroup_edit', array('title' => $linkGroup->getTitle()));
        }

        return $this->render('linkgroup/edit.html.twig', array(
            'linkGroup' => $linkGroup,
            'edit_form' => $editForm->createView(),
        ));
    }

}
