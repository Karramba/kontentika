<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Link;
use AppBundle\Entity\Vote;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * Vote controller.
 *
 * @Route("/vote")
 */
class VoteController extends Controller
{
    /**
     * Lists all Vote entities.
     *
     * @Route("/{voteType}/{voteFor}/{uniqueId}", requirements={"voteType": "(up|down)"}, name="vote")
     * @Method("GET")
     */
    public function voteAction(Request $request, $voteType, $voteFor, $uniqueId)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $content = $em->getRepository("AppBundle:" . ucfirst($voteFor))
                ->createQueryBuilder("c")
                ->select("c, uv, dv, uuv, udv")
                ->leftjoin("c.upvotes", "uv")
                ->leftjoin("c.downvotes", "dv")
                ->leftjoin("uv.user", "uuv")
                ->leftjoin("dv.user", "udv")
                ->andwhere("c.uniqueId = :uniqueId")->setParameter("uniqueId", $uniqueId)
                ->getQuery()->getOneOrNullResult()
            ;

            if (!$content) {
                return new JsonResponse();
            }

            if ($this->getUser() instanceof User && $content->getUser() != $this->getUser()) {
                $addVote = "add" . ucfirst($voteType) . "vote";

                $voted = $this->voted($content);

                $newVote = new Vote();
                $newVote->setUser($this->getUser());

                if (!$voted) {
                    $content->$addVote($newVote);
                } else {
                    $getVotes = "get" . $voted['type'] . "votes";
                    // $content->$getVotes()->removeElement($voted['vote']);
                    $removeVote = "remove" . $voted['type'] . "vote";
                    $content->$removeVote($voted['vote']);
                    if ($voted['type'] != $voteType) {
                        $content->$addVote($newVote);
                    }
                }

                $votes = $content->getUpvotes()->count() - $content->getDownvotes()->count();
                if ($content instanceof Link && $content->getMainpageAt() == null && $votes >= $this->getParameter('mainpage_entry_votes')) {
                    $content->setMainpageAt(new \DateTime());
                }

                $em->persist($content);
                $em->flush();
            }

        } catch (Exception $e) {
            // return new JsonResponse($e->getMessage());
            return null;
        }

        $result = [
            "id" => $uniqueId,
            "upvotes" => $content->getUpvotes()->count(),
            "downvotes" => $content->getDownvotes()->count(),
            "myvote" => $this->voted($content)['type'],
        ];

        return new JsonResponse($result);
    }

    private function voted($content)
    {
        foreach ($content->getUpvotes() as $upvote) {
            if ($upvote->getUser() == $this->getUser()) {
                return [
                    'type' => 'up',
                    'vote' => $upvote,
                ];
            }
        }

        foreach ($content->getDownvotes() as $downvote) {
            if ($downvote->getUser() == $this->getUser()) {
                return [
                    'type' => 'down',
                    'vote' => $downvote,
                ];
            }
        }
        return null;
    }

    /**
     * @Route("/votes", name="vote_voters_list", requirements={"voteType": "(up|down)"})
     */
    public function votersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $timehelper = $this->get('time.datetime_formatter');

        $voteType = $request->request->get('voteType');
        $contentType = $request->request->get('contentType');
        $uniqueId = $request->request->get('uniqueId');

        try {
            $content = $em->getRepository("AppBundle:" . ucfirst($contentType))
                ->createQueryBuilder("c")
                ->select("c, uv, dv, uuv, udv")
                ->leftjoin("c.upvotes", "uv")
                ->leftjoin("c.downvotes", "dv")
                ->leftjoin("uv.user", "uuv")
                ->leftjoin("dv.user", "udv")
                ->andwhere("c.uniqueId = :uniqueId")->setParameter("uniqueId", $uniqueId)
                ->getQuery()->getOneOrNullResult();

            $getVotes = "get" . ucfirst($voteType) . "votes";

            $responseVotes = array();
            $now = new \DateTime();

            $votes = $content->$getVotes()->toArray();
            foreach ($votes as $vote) {
                $username = $vote->getUser()->getUsername();
                $userprofile = $this->generateUrl('user_show', array('username' => $vote->getUser()->getUsername()));
                $dateago = $timehelper->formatDiff($vote->getVoted(), $now);

                $responseVotes[] = "${username} (${dateago})";
            }
            return new JsonResponse($responseVotes);

        } catch (Exception $e) {
            return new JsonResponse(array());
        }

        return new JsonResponse(array());

    }
}
