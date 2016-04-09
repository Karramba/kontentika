<?php

namespace UserBundle\Twig;

use UserBundle\Service\UserService;

class UserExtension extends \Twig_Extension
{
    private $userService;

    /**
     * @param EntityManager $em
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('findUsers', array($this, 'findUsers'), array('is_safe' => array('html'))),
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
        return array();
    }

    /**
     * Checks for any user mentioned by "@"
     *
     * @param $content
     */
    public function findUsers($content)
    {
        preg_match_all("/\@[a-ż0-9\_\-]+/i", $content, $result);

        if (isset($result[0]) && sizeof($result[0]) > 0) {
            $users = array_unique($result[0]);
            $foundUsernames = $this->userService->findMentionedUsers($users);
            foreach ($foundUsernames as $username) {
                $content = str_replace($username, "<a href=/u/" . $username . ">{$username}</a>", $content);
            }

        }
        return $content;
    }

    public function getName()
    {
        return 'user_extension';
    }
}
