<?php

namespace UserBundle\Service;

use Doctrine\ORM\EntityManager;
use Predis\Client;
use UserBundle\Entity\User;

class UserService
{
    /**
     * @var mixed
     */
    private $em;
    /**
     * @var mixed
     */
    private $redis;

    /**
     * @param EntityManager $em
     * @param Client $redis
     */
    public function __construct(EntityManager $em, Client $redis)
    {
        $this->em = $em;
        $this->redis = $redis;
    }

    /**
     * @param array $users
     */
    public function findMentionedUsers(array $users)
    {
        $foundUsers = array();

        foreach ($users as $mentionedUser) {
            $username = substr($mentionedUser, 1); // username without "@"
            $user = $this->redis->get('user:' . $username);

            if (!$user) {
                $user = $this->em->getRepository("UserBundle:User")->findOneByUsername($username);
                if ($user instanceof User) {
                    $this->redis->set('user:' . $username, $user->getUsername());
                }
            } else {
                $foundUsers[] = $username;
            }
        }
        return $foundUsers;
    }
}
