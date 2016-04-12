<?php

namespace UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Just find users by string username
     *
     * @param $username
     * @return mixed
     */
    public function findUsers($username)
    {
        return $this->createQueryBuilder("u")
            ->select('u.username')
            ->where("u.username LIKE :username")->setParameter("username", "{$username}%")
            ->getQuery()->getArrayResult();
    }

}
