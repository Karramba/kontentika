<?php

namespace AppBundle\Twig;

use AppBundle\Entity\LinkGroup;
use AppBundle\Media\VideoEmbedder;
use AppBundle\Service\AuthService;
use Doctrine\ORM\EntityManager;
use Predis\Client;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Twig extension
 */
class KickExtension extends \Twig_Extension
{
    private $requestStack;
    private $em;
    private $authService;
    private $redis;
    private $videoEmbedder;
    private $linkClass;

    /**
     * Defines detected href target
     *
     * @var string
     */
    private $target = "_blank";

    /**
     * @param RequestStack $requestStack
     * @param EntityManager $em
     */
    public function __construct(RequestStack $requestStack, EntityManager $em, AuthService $authService, Client $redis)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $filter = $this->em->getFilters()->enable('softdeleteable');
        $this->authService = $authService;
        $this->redis = $redis;

        $this->videoEmbedder = new VideoEmbedder();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'createUrls',
                array($this, 'autoConvertUrls'),
                array(
                    'pre_escape' => 'html',
                    'is_safe' => array('html'),
                )
            ),
            new \Twig_SimpleFilter(
                'embedIfVideo',
                array($this, 'embedVideo'),
                array(
                    'pre_escape' => 'html',
                    'is_safe' => array('html'),
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('leftblock', array($this, 'leftblock'), array(
                'is_safe' => array('html'),
                'needs_environment' => true,
            )),
            new \Twig_SimpleFunction('isGroupActive', array($this, 'isGroupActive'), array()),
            new \Twig_SimpleFunction('isRouteActive', array($this, 'isRouteActive'), array()),
            new \Twig_SimpleFunction('bestRated', array($this, 'bestRated'), array()),
            new \Twig_SimpleFunction('lastComments', array($this, 'lastComments'), array()),
            new \Twig_SimpleFunction('countBR', array($this, 'countBR'), array()),
            new \Twig_SimpleFunction('class', array($this, 'getClass')),
            new \Twig_SimpleFunction('moderationToolsAccess', array($this, 'moderationToolsAccess')),
        );
    }

    /**
     * Left block renderer - used in base.html.twig
     *
     * @param \Twig_Environment $twig
     * @return mixed
     */
    public function leftblock(\Twig_Environment $twig)
    {
        $route = $this->requestStack->getCurrentRequest()->get('_route');
        $prefix = substr($route, 0, strpos($route, '_'));

        try {
            $twigPath = "::leftblock/" . $prefix . ".html.twig";
            // var_dump($twigPath);
            $twig->loadTemplate($twigPath);
            return $twigPath;
        } catch (\Exception $e) {
            return "::leftblock/link.html.twig";
        }

    }

    /**
     * Compares current group to route prefix
     *
     * @param $groupRoute
     */
    public function isGroupActive($groupRoute)
    {
        $route = $this->requestStack->getCurrentRequest()->get('_route');
        $prefix = substr($route, 0, strpos($route, '_'));

        if ($groupRoute == $prefix) {
            return "active";
        }
    }

    /**
     * * Compares current route to given route name
     *
     * @param $route
     */
    public function isRouteActive(array $routes = array())
    {
        foreach ($routes as $route) {
            if ($this->requestStack->getCurrentRequest()->get('_route') == $route) {
                return "active";
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function bestRated($days)
    {
        return $this->em->getRepository("AppBundle:Link")->findBestRated($days);
    }

    /**
     * @param $commentsNumber
     * @return mixed
     */
    public function lastComments($commentsNumber)
    {
        return $this->em->getRepository("AppBundle:Comment")->findLastComments($commentsNumber);
    }

    /**
     * @param $string
     */
    public function countBR($string)
    {
        return substr_count($string, "\n");
    }

    /**
     * @param $object
     */
    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    /**
     * @param LinkGroup $group
     * @return mixed
     */
    public function moderationToolsAccess(LinkGroup $group)
    {
        return $this->authService->haveModerationToolsAccess($group);
    }

    public function embedVideo($url)
    {
        $embedCode = $this->videoEmbedder->embedVideo($url);
        if (!is_null($embedCode)) {
            return '<div class="row link box thumbnail">' . $embedCode . '</div>';
        }
        return false;
    }

    /**
     * method that finds different occurrences of urls or email addresses in a string.
     *
     * @param string $string input string
     *
     * @return string with replaced links
     */
    public function autoConvertUrls($string)
    {
        $pattern = '/(^|[^"])(http|https|ftp|ftps)\:\/\/[-a-zA-Zа-яёА-ЯЁ0-9\-\.]+\.[a-zA-Z]{2,3}(\/[-a-zA-Zа-яёА-ЯЁ0-9\-\.\?\=\/\&\#\[\]\%\~\:\!\$\;\,\+\@\;\']*)?/u';
        $stringFiltered = preg_replace_callback($pattern, array($this, 'callbackReplace'), $string);
        return $stringFiltered;
    }

    /**
     * @param $matches
     * @return mixed
     */
    public function callbackReplace($matches)
    {
        $url = substr($matches[0], 1);

        $video = $this->videoEmbedder->embedVideo($url);
        if (!is_null($video)) {
            return $video;
        }

        return $matches[1] . '<a href="' . $url . '" class="' . $this->linkClass . '" target="' . $this->target . '">' . $url . '</a>';
    }

    public function getName()
    {
        return 'kick_extension';
    }

}
