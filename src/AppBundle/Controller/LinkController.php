<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Domain;
use AppBundle\Entity\Link;
use AppBundle\Entity\LinkGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Link controller.
 *
 * @Route("/")
 */
class LinkController extends Controller
{
    /**
     * Lists all Link entities.
     *
     * @Route("/", name="link_index", defaults={"best" = true})
     * @Route("/p/{page}", name="link_index_page", defaults={"best" = true})
     * @Method("GET")
     */
    public function indexAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:Link')
            ->findBestLinks($this->getUser(), $page, $this->getParameter('content_per_page'));

        return $this->render('link/index.html.twig', array(
            'page' => $page,
            'pages' => ceil($result['linksNumber'] / $this->getParameter('content_per_page')),
            'links' => $result['links'],
            'linksNumber' => $result['linksNumber'],
            'paginationRoute' => "link_index_page",
            'subtitle' => "link.best",
        ));
    }

    /**
     * @Route("/newest", name="link_newest")
     * @Route("/newest/p/{page}", name="link_newest_page")
     * @Method("GET")
     */
    public function newestAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:Link')
            ->findNewestLinks($this->getUser(), $page, $this->getParameter('content_per_page'));

        return $this->render('link/index.html.twig', array(
            'page' => $page,
            'pages' => ceil($result['linksNumber'] / $this->getParameter('content_per_page')),
            'links' => $result['links'],
            'linksNumber' => $result['linksNumber'],
            'paginationRoute' => "link_newest_page",
            'subtitle' => "link.newest",
        ));
    }

    /**
     * @Route("/rising", name="link_rising")
     * @Route("/rising/p/{page}", name="link_rising_page")
     * @Method("GET")
     */
    public function risingAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:Link')
            ->findRisingLinks($this->getUser(), $page, $this->getParameter('content_per_page'));

        return $this->render('link/index.html.twig', array(
            'page' => $page,
            'pages' => ceil($result['linksNumber'] / $this->getParameter('content_per_page')),
            'links' => $result['links'],
            'linksNumber' => $result['linksNumber'],
            'paginationRoute' => "link_rising_page",
            'subtitle' => "link.rising",
        ));
    }

    /**
     * Lists all Link entities.
     *
     * @Route("/g/{title}", name="link_group_show")
     * @Route("/g/{title}/p/{page}", name="link_group_show_page")
     */
    public function showGroupAction(Request $request, LinkGroup $linkGroup, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository("AppBundle:Link")->findAllGroupLinks($linkGroup, $page, $this->getParameter('content_per_page'));

        if (!$result) {
            throw $this->createNotFoundException();
        }

        return $this->render('link/index.html.twig', array(
            'page' => $page,
            'pages' => ceil($result['linksNumber'] / $this->getParameter('content_per_page')),
            'links' => $result['links'],
            'linksNumber' => $result['linksNumber'],
            'paginationRoute' => 'link_group_show_page',
            'linkgroup' => $linkGroup,
        ));

    }
    /**
     * Creates a new Link entity.
     *
     * @Route("/new", name="link_new")
     * @Route("/new/{groupTitle}", name="link_new_with_linkgroup")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction(Request $request, $groupTitle = null)
    {
        $em = $this->getDoctrine()->getManager();

        $link = new Link();

        if ($groupTitle != null) {
            $linkGroup = $em->getRepository("AppBundle:LinkGroup")->findOneByTitle($groupTitle);
            $link->setGroup($linkGroup);
        }

        $form = $this->createForm('AppBundle\Form\LinkType', $link, array('em' => $em));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $link->setDomain($this->findDomain($link->getUrl()));
            $this->downloadAndSaveImage($link);

            $this->getUser()->addLink($link);

            $em->persist($link);
            $em->flush();

            $this->addFlash('success', 'link.added_flash');

            return $this->redirectToRoute('link_show', array('slug' => $link->getSlug(), 'uniqueId' => $link->getUniqueId()));
        }

        return $this->render('link/new.html.twig', array(
            'link' => $link,
            'form' => $form->createView(),
        ));
    }

    private function findDomain($url)
    {
        $em = $this->getDoctrine()->getManager();

        $domain = null;
        try {
            $data = parse_url($url);
            if (isset($data['host'])) {
                $domain = $em->getRepository("AppBundle:Domain")->findOneByName($data['host']);
                if (!$domain) {
                    $domain = new Domain();
                    $domain->setName($data['host']);
                }
            }
        } catch (Exception $e) {

        }
        return $domain;
    }

    /**
     * Downloads image and saves to storage
     *
     */
    private function downloadAndSaveImage(Link $link)
    {
        $imagesDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads';

        if (filter_var($link->getThumbnail(), FILTER_VALIDATE_URL) && $image = file_get_contents($link->getThumbnail())) {
            $path = sys_get_temp_dir() . "/" . $link->getUniqueId();
            if (file_put_contents($path, $image)) {
                try {
                    $file = new File($path);
                    $extension = $file->guessExtension();
                    $newName = $link->getUniqueId() . "." . $extension;
                    $file->move($imagesDir, $newName);
                    $link->setThumbnail($newName);
                } catch (Exception $e) {
                    throw new Symfony\Component\HttpKernel\Exception\HttpException(500, $e->getMessage());
                    // var_dump($e->getMessage());
                    // exit;
                }
            }

        }
    }

    /**
     * Finds and displays a Link entity.
     *
     * @Route("/l/{uniqueId}", name="link_short")
     * @Method("GET")
     */
    public function shortLinkAction(Link $link)
    {
        return $this->redirectToRoute('link_show', array(
            'uniqueId' => $link->getUniqueId(),
            'slug' => $link->getSlug(),
        ));
    }

    /**
     * Finds and displays a Link entity.
     *
     * @Route("/l/{uniqueId}/{slug}", name="link_show")
     * @Method("GET")
     */
    public function showAction($uniqueId, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        $link = $em->getRepository("AppBundle:Link")
            ->createQueryBuilder("l")
            ->select("l, uv, dv, d, g")
            ->leftjoin("l.upvotes", "uv")
            ->leftjoin("l.downvotes", "dv")
            ->leftjoin("l.group", "g")
            ->leftjoin("l.domain", "d")
            ->where("l.uniqueId = :uniqueId")->setParameter("uniqueId", $uniqueId)
            ->andWhere("l.slug = :slug")->setParameter("slug", $slug)
            ->getQuery()->getOneOrNullResult();

        if (!$link) {
            throw $this->createNotFoundException();
        }

        $filter = $em->getFilters()->disable('softdeleteable');
        // $filter->disableForEntity("AppBundle:Comment");

        $comments = $em->getRepository("AppBundle:Comment")
            ->createQueryBuilder("c")
            ->select("c, uv, dv, u, cc, cuv, cdv, cu")
            ->leftjoin("c.upvotes", "uv")
            ->leftjoin("c.downvotes", "dv")
            ->join("c.user", "u")
            ->leftjoin("c.children", "cc")
            ->leftjoin("cc.upvotes", "cuv")
            ->leftjoin("cc.downvotes", "cdv")
            ->leftjoin("cc.user", "cu")
            ->where("c.parent is NULL")
            ->andWhere("c.link = :link")->setParameter("link", $link)
            ->getQuery()->getResult();

        $deleteFormView = null;
        if ($this->getUser() == $link->getUser()) {
            $deleteFormView = $this->createDeleteForm($link)->createView();
        }
        $comment = new Comment();
        $comment_form = $this->createForm('AppBundle\Form\CommentType', $comment, array(
            'action' => $this->generateUrl('comment_new', array('linkUniqueId' => $link->getUniqueId())),
        ));

        return $this->render('link/show.html.twig', array(
            'link' => $link,
            'comments' => $comments,
            'delete_form' => $deleteFormView,
            'comment_form' => $comment_form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Link entity.
     *
     * @Route("/edit/{uniqueId}", name="link_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, Link $link)
    {
        if ($link->getUser() != $this->getUser() &&
            !$this->get('auth.service')->haveModerationToolsAccess($link->getGroup())) {
            throw $this->createNotFoundException();
        }

        if ($link->getMainpageAt() != null &&
            !$this->get('auth.service')->haveModerationToolsAccess($link->getGroup())) {
            throw $this->createNotFoundException($this->get('translator')->trans("link.edition_impossible"));
        }

        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($link);
        $editForm = $this->createForm('AppBundle\Form\LinkType', $link, array('em' => $em));
        $editForm->remove('url');
        $editForm->remove('group');
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->persist($link);
            $em->flush();

            $this->addFlash('success', 'link.edited_and_saved');

            return $this->redirectToRoute('link_show', array('uniqueId' => $link->getUniqueId(), 'slug' => $link->getSlug()));
        }

        return $this->render('link/edit.html.twig', array(
            'link' => $link,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Link entity.
     *
     * @Route("/d/{uniqueId}", name="link_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction(Request $request, Link $link)
    {
        if ($link->getUser() != $this->getUser() &&
            !$this->get('auth.service')->haveModerationToolsAccess($link->getGroup())) {
            $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();

        if ($link->getUser() != $this->getUser()) {
            $this->get('notification.service')->addReplyNotification($link, "link_deleted");
        }

        $em->remove($link);
        $em->flush();

        $this->addFlash('success', 'link.deleted');
        // }

        return $this->redirectToRoute('link_index');
    }

    /**
     * Creates a form to delete a Link entity.
     *
     * @param Link $link The Link entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Link $link)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('link_delete', array('uniqueId' => $link->getUniqueId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @Route("/l/generate_title", name="link_generate_title")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function generateContent(Request $request)
    {
        $title = $description = $thumbnail = null;

        try {
            $link = $request->request->get('url');
            if (!strstr($link, 'http')) {
                $link = 'http://' . $link;
            }
            if ($content = file_get_contents($link)) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $contentType = $finfo->buffer($content);

                if ($contentType == "text/html") {
                    $crawler = new Crawler($content);
                    $title = $crawler->filterXPath('html/head/title')->text();
                    $description = $crawler->filterXPath("//meta[@name='description']")->attr('content');
                    $thumbnail = $crawler->filterXPath("//meta[@property='og:image']")->attr('content');
                } elseif (strstr($contentType, "image/")) {
                    $thumbnail = $link;
                }
            }

        } catch (\Exception $e) {
            // var_dump($e->getMessage());
        }

        return new JsonResponse(array('title' => trim($title), 'description' => trim($description), 'thumbnail' => $thumbnail));
    }
}
