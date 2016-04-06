<?php

namespace UserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * User controller.
 *
 * @Route("/u")
 */
class UserController extends Controller
{
    /**
     * Finds and displays a User entity.
     *
     * @Route("/{username}", name="user_show")
     * @Route("/{username}/p-{page}", name="user_show_page", requirements={"page": "[0-9]+"})
     * @Method("GET")
     */
    public function showAction(User $user, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        if ($page < 1) {
            $page = 1;
        }

        $entries = $em->getRepository("AppBundle:Entry")->createQueryBuilder("e")
            ->select("e, g, dv, uv")
            ->join("e.group", "g")
            ->leftjoin("e.upvotes", "uv")
            ->leftjoin("e.downvotes", "dv")
            ->where("e.user = :user")->setParameter("user", $user)
            ->getQuery()->getResult();

        $links = $em->getRepository("AppBundle:Link")->createQueryBuilder("l")
            ->select("l, g, dv, uv, d, c")
            ->join("l.group", "g")
            ->leftjoin("l.upvotes", "uv")
            ->leftjoin("l.downvotes", "dv")
            ->join("l.domain", "d")
            ->leftjoin("l.comments", "c")
            ->where("l.user = :user")->setParameter("user", $user)
            ->getQuery()->getResult();

        $comments = $em->getRepository("AppBundle:Comment")->createQueryBuilder("c")
            ->select("c, dv, uv")
            ->leftjoin("c.upvotes", "uv")
            ->leftjoin("c.downvotes", "dv")
            ->where("c.user = :user")->setParameter("user", $user)
            ->getQuery()->getResult();

        $merged = new ArrayCollection(
            array_merge(
                $comments,
                $entries,
                $links
                // $user->getLinkGroups()->toArray()
            )
        );

        $iterator = $merged->getIterator();

        $iterator->uasort(function ($a, $b) {
            return ($a->getAdded() > $b->getAdded()) ? -1 : 1;
        });

        $contents = new ArrayCollection(iterator_to_array($iterator));
        $pages = ceil(count($contents) / $this->getParameter('content_per_page'));

        $contents = $contents->slice(($page - 1) * $this->getParameter('content_per_page'), $this->getParameter('content_per_page'));

        return $this->render('user/show.html.twig', array(
            'user' => $user,
            'contents' => $contents,
            'page' => $page,
            'paginationRoute' => 'user_show_page',
            'pages' => $pages,
        ));
    }

    /**
     * @Route("/json", name="users_json")
     * @Method("POST")
     */
    public function indexAction(Request $request)
    {
        $usernames = array();
        $username = $request->request->get('username');
        if (strlen($username) >= 2) {
            $em = $this->getDoctrine()->getManager();
            $usernames = array_map('current', $em->getRepository("UserBundle:User")->findUsers($username));
        }
        return new JsonResponse($usernames);
    }
}
