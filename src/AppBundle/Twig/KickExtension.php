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
    public function isRouteActive($route)
    {
        if ($this->requestStack->getCurrentRequest()->get('_route') == $route) {
            return "active";
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
            return '<div class="thumbnail">' . $embedCode . '</div>';
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
        $pattern = '/(href="|src=")?([-a-zA-Zа-яёА-ЯЁ0-9@:%_\+.~#?&\/\/=]{2,256}\.[a-zа-яё]{2,4}\b(\/?[-\p{L}0-9@:%_\+.~#?&\/\/=\(\),]*)?)/u';
        $stringFiltered = preg_replace_callback($pattern, array($this, 'callbackReplace'), $string);
        return $stringFiltered;
    }

    /**
     * @param $matches
     * @return mixed
     */
    public function callbackReplace($matches)
    {
        if ($matches[1] !== '') {
            return $matches[0]; // don't modify existing <a href="">links</a> and <img src="">
        }
        $url = $matches[2];

        $video = $this->videoEmbedder->embedVideo($url);

        if (!is_null($video)) {
            return $video;
        }

        $urlWithPrefix = $matches[2];
        if (strpos($url, '@') !== false) {
            $urlWithPrefix = 'mailto:' . $url;
        } elseif (strpos($url, 'https://') === 0) {
            $urlWithPrefix = $url;
        } elseif (strpos($url, 'http://') !== 0) {
            $urlWithPrefix = 'http://' . $url;
        }

        // $style = ($this->debugMode) ? ' style="color:' . $this->debugColor . '"' : '';
        // ignore tailing special characters
        // TODO: likely this could be skipped entirely with some more tweakes to the regular expression
        if (preg_match("/^(.*)(\.|\,|\?)$/", $urlWithPrefix, $matches)) {
            $urlWithPrefix = $matches[1];
            $url = substr($url, 0, -1);
            $punctuation = $matches[2];
        } else {
            $punctuation = '';
        }
        return '<a href="' . $urlWithPrefix . '" class="' . $this->linkClass . '" target="' . $this->target . '">' . $url . '</a>' . $punctuation;
    }

    public function getName()
    {
        return 'kick_extension';
    }

}
