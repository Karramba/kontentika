<?php

namespace AppBundle\Repository;

/**
 * CommentRepository
 */
class CommentRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Last comments list
     *
     * @param $commentsNumber
     * @return mixed
     */
    public function findLastComments($commentsNumber)
    {
        $qb = $this->createQueryBuilder("c");

        $query = $qb
            ->select("c, u, l")
            ->join("c.user", "u")
            ->join("c.link", "l")
            ->orderBy("c.added", "DESC");

        $query->setMaxResults(5);

        return $query->getQuery()->getResult();
    }
}
