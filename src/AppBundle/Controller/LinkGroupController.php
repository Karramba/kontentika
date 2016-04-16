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
            'linkgroups' => $result['linkgroups'],
            'page' => $page,
            'pages' => ceil($result['linkgroupsNumber'] / $this->getParameter('content_per_page')),
            'linkgroupsNumber' => $result['linkgroupsNumber'],
            'paginationRoute' => 'linkgroup_index_page',
            'route_params' => array(),
        ));
    }

    /**
     * Lists all Link entities.
     *
     * @Route("/g/{title}", name="linkgroup_show")
     * @Route("/g/{title}/p/{page}", name="linkgroup_show_page")
     */
    public function showGroupAction(Request $request, LinkGroup $linkgroup, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository("AppBundle:Link")->findAllGroupLinks($linkgroup, $page, $this->getParameter('content_per_page'));

        if (!$result) {
            throw $this->createNotFoundException();
        }

        return $this->render('link/index.html.twig', array(
            'page' => $page,
            'pages' => ceil($result['linksNumber'] / $this->getParameter('content_per_page')),
            'links' => $result['links'],
            'linksNumber' => $result['linksNumber'],
            'linkgroup' => $linkgroup,
            'paginationRoute' => 'linkgroup_show_page',
            'route_params' => array('title' => $linkgroup->getTitle()),
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
            'linkgroups' => $result['linkgroups'],
            'page' => $page,
            'pages' => ceil($result['linkgroupsNumber'] / $this->getParameter('content_per_page')),
            'linkgroupsNumber' => $result['linkgroupsNumber'],
            'paginationRoute' => 'my_linkgroups_page',
            'route_params' => array(),
        ));
    }

    /**
     * Lists all LinkGroup entities.
     *
     * @Route("/g-u/{username}", name="user_linkgroups")
     * @Route("/g-u/{username}/{page}", name="user_linkgroups_page", requirements={"page": "[0-9]+"})
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function userGroupsAction($page = 1, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:LinkGroup')
            ->findUserGroups($page, $this->getParameter('content_per_page'), $user);

        return $this->render('linkgroup/index.html.twig', array(
            'linkgroups' => $result['linkgroups'],
            'page' => $page,
            'pages' => ceil($result['linkgroupsNumber'] / $this->getParameter('content_per_page')),
            'linkgroupsNumber' => $result['linkgroupsNumber'],
            'user' => $user,
            'paginationRoute' => 'user_linkgroups_page',
            'route_params' => array('username' => $user->getUsername()),
        ));
    }

    /**
     * Creates a new LinkGroup entity.
     *
     * @Route("/g-new", name="linkgroup_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction(Request $request)
    {
        $linkgroup = new LinkGroup();
        $form = $this->createForm('AppBundle\Form\LinkGroupType', $linkgroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $linkgroup->setOwner($this->getUser());
            $em->persist($linkgroup);
            $em->flush();
            $this->addFlash('success', 'linkgroup.created_flash');

            return $this->redirectToRoute('linkgroup_show', array('title' => $linkgroup->getTitle()));
        }

        return $this->render('linkgroup/new.html.twig', array(
            'linkgroup' => $linkgroup,
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
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, LinkGroup $linkgroup)
    {
        if (!$this->get('auth.service')->isGroupAdmin($linkgroup)) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\LinkGroupEditType', $linkgroup, array('em' => $em));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            foreach ($linkgroup->getModerators() as $moderator) {
                if ($moderator == $this->getUser()) {
                    $linkgroup->removeModerator($moderator);
                    $this->addFlash('warning', 'linkgroup.groupowner_as_moderator_forbidden');
                }
            }
            $em->persist($linkgroup);
            $em->flush();

            $this->addFlash('success', 'linkgroup.settings_saved');

            return $this->redirectToRoute('linkgroup_edit', array('title' => $linkgroup->getTitle()));
        }

        return $this->render('linkgroup/edit.html.twig', array(
            'linkgroup' => $linkgroup,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Lists all LinkGroup entities.
     *
     * @Route("/g/{title}/subscribe", name="linkgroup_subscribe")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function subscribeAction(LinkGroup $linkgroup)
    {
        $em = $this->getDoctrine()->getManager();

        $linkgroup->addSubscribedUser($this->getUser());

        $em->persist($linkgroup);
        $em->flush();

        $this->addFlash('success', 'linkgroup.subscribed');

        return $this->redirectToRoute('linkgroup_show', array(
            'title' => $linkgroup->getTitle(),
        ));
    }

    /**
     * Lists all LinkGroup entities.
     *
     * @Route("/g/{title}/unsubscribe", name="linkgroup_unsubscribe")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function unsubscribeAction(LinkGroup $linkgroup)
    {
        $em = $this->getDoctrine()->getManager();

        $linkgroup->removeSubscribedUser($this->getUser());

        $em->persist($linkgroup);
        $em->flush();

        $this->addFlash('success', 'linkgroup.unsubscribed');

        return $this->redirectToRoute('linkgroup_show', array(
            'title' => $linkgroup->getTitle(),
        ));
    }
}
