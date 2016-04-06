<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * EntryRepository
 */
class EntryRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Last added entries - paginated
     *
     * @param $page
     * @param $entriesPerPage
     */
    public function findEntries($page, $entriesPerPage)
    {
        $qb = $this->createQueryBuilder("e");
        $query = $qb
            ->select("e, u, g, uv, dv, c, cuv, cdv")
            ->join("e.user", "u")
            ->join("e.group", "g")
            ->leftjoin("e.upvotes", "uv")
            ->leftjoin("e.downvotes", "dv")
            ->leftjoin("e.children", "c")
            ->leftjoin("c.upvotes", "cuv")
            ->leftjoin("c.downvotes", "cdv")
            ->orderBy("e.added", "DESC")
            ->where("e.parent IS NULL")
        ;

        $result = $query->setFirstResult(($page - 1) * $entriesPerPage)
            ->setMaxResults($entriesPerPage);

        $entries = new Paginator($result, $fetchJoinCollection = true);

        return [
            'entries' => $entries->getQuery()->getResult(),
            'entriesNumber' => count($entries),
        ];

    }

    /**
     * Return specific entry (find by uniqueId) or nothing
     *
     * @param $uniqueId
     * @return Entry|null
     */
    public function findEntry($uniqueId)
    {
        $qb = $this->createQueryBuilder("e");
        $query = $qb
            ->select("e, u, g, uv, dv, c, cuv, cdv")
            ->join("e.user", "u")
            ->join("e.group", "g")
            ->leftjoin("e.upvotes", "uv")
            ->leftjoin("e.downvotes", "dv")
            ->leftjoin("e.children", "c")
            ->leftjoin("c.upvotes", "cuv")
            ->leftjoin("c.downvotes", "cdv")
            ->where("e.uniqueId = :uniqueId")->setParameter("uniqueId", $uniqueId)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }
}
