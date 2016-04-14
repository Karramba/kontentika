<?php

namespace AppBundle\Controller;

use AppBundle\Entity\LinkGroup;
use AppBundle\Entity\Notification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * LinkGroup controller.
 *
 * @Route("/n")
 */
class NotificationController extends Controller
{
    /**
     * @Route("/dropdown")
     * @Template("notification/dropdown.html.twig")
     */
    public function dropdownAction()
    {
        return array();
    }

    /**
     * @Route("/markasread", name="notification_mark_as_read")
     */
    public function markAsReadAction()
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository("AppBundle:Notification")
            ->createQueryBuilder("n")
            ->update("AppBundle:Notification", "n")
            ->set("n.unread", 0)
            ->where("n.user = :user")->setParameter("user", $this->getUser());

        $query->getQuery()->execute();

        return new JsonResponse("ok");
    }

    /**
     * @Route("/redirect/{uniqueId}", name="notification_redirect")
     */
    public function redirectAction(Notification $notification)
    {
        $em = $this->getDoctrine()->getManager();
        $filter = $em->getFilters()->disable('softdeleteable');

        $notification->setUnread(false);
        $em->persist($notification);
        $em->flush();

        try {
            $content = $em->getRepository("AppBundle:" . $notification->getContentType())
                ->findOneByUniqueId($notification->getContentUniqueId());

            switch ($notification->getContentType()) {
                case 'Entry':
                    $url = $this->generateUrl('entry_show', array(
                        'uniqueId' => ($content->getParent() != null) ? $content->getParent()->getUniqueId() : $content->getUniqueId(),
                    ));
                    $url .= "#" . $content->getUniqueId();
                    break;
                case 'Comment':
                    $url = $this->generateUrl('link_show', array(
                        'uniqueId' => $content->getLink()->getUniqueId(),
                        'slug' => $content->getLink()->getSlug(),
                    ));
                    $url .= "#" . $content->getUniqueId();
                    break;
                default:
                    $url = $this->generateUrl('link_index');
                    break;
            }

            return $this->redirect($url);
        } catch (\Exception $e) {
            // var_dump($e->getMessage());exit;
            throw $this->createNotFoundException();
        }

    }
}
