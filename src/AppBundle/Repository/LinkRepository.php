<?php

namespace AppBundle\Repository;

use AppBundle\Entity\LinkGroup;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * LinkRepository
 */
class LinkRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $em
     * @param ClassMetadata $class
     */
    public function __construct($em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    public function findLinks(array $links = array())
    {
        $query = $this->createQueryBuilder("l")
            ->select("l, c, uv, dv, g, u, d")
            ->leftjoin("l.comments", "c")
            ->leftjoin("l.group", "g")
            ->leftjoin("l.user", "u")
            ->leftjoin("l.domain", "d")
            ->leftjoin("l.upvotes", "uv")
            ->leftjoin("l.downvotes", "dv")
            ->where("(l.totalUpvotes - l.totalDownvotes) > -5")
            ->andWhere("l IN (:links)")->setParameter("links", $links)
        ;
        return $query;
    }

    /**
     * @param $user
     * @param $page
     * @param $linksPerPage
     * @return mixed
     */
    public function findNewestLinks($page, $linksPerPage, $linkgroup = null)
    {
        $query = $this->createQueryBuilder("l")
            ->select("l")
            ->where("l.mainpageAt is null")
            ->orderBy("l.id", "DESC");

        if ($linkgroup instanceof LinkGroup) {
            $query->andWhere("l.group = :linkgroup")->setParameter("linkgroup", $linkgroup);
        } else {
            /* Show some links on global newest */
            $query
                ->andWhere("l.added > :newestExpirationTime")
                ->setParameter("newestExpirationTime", new \DateTime('-52 hours'));
        }
        $result = $query->setFirstResult(($page - 1) * $linksPerPage)->setMaxResults($linksPerPage);
        $links = new Paginator($result, $fetchJoinCollection = true);

        $resultQuery = $this->findLinks($links->getQuery()->getResult())->addOrderBy("l.added", "DESC");

        return [
            'links' => $resultQuery->getQuery()->getResult(),
            'linksNumber' => count($links),
        ];
    }

    /**
     * Returns only links that upvotes exceeded parameters.yml mainpage_entry_votes value
     *
     * @param $user
     * @param $page
     * @param $linksPerPage
     * @return mixed
     */
    public function findBestLinks($page, $linksPerPage, $linkgroup = null)
    {
        $query = $this->createQueryBuilder("l")
            ->select("l")
            ->where("(l.totalUpvotes - l.totalDownvotes) > 0")
            ->andWhere("l.mainpageAt is not null")
            ->orderBy("l.mainpageAt", "DESC");

        if ($linkgroup instanceof LinkGroup) {
            $query->andWhere("l.group = :linkgroup")->setParameter("linkgroup", $linkgroup);
        }

        $result = $query->setFirstResult(($page - 1) * $linksPerPage)->setMaxResults($linksPerPage);
        $links = new Paginator($result, $fetchJoinCollection = true);

        $resultQuery = $this->findLinks($links->getQuery()->getResult())
            ->addOrderBy("l.mainpageAt", "DESC");

        return [
            'links' => $resultQuery->getQuery()->getResult(),
            'linksNumber' => count($links),
        ];
    }

    /**
     * @param $user
     * @param $page
     * @param $linksPerPage
     * @return mixed
     */
    public function findRisingLinks($page, $linksPerPage, $linkgroup = null)
    {
        $query = $this->createQueryBuilder("l")
            ->select("l")
            ->addSelect("(l.totalUpvotes - l.totalDownvotes) as HIDDEN _total_votes")
            ->where("l.mainpageAt is null")
            ->andWhere("l.added > :newestExpirationTime")
            ->orderBy("_total_votes", "DESC")
        ;

        if ($linkgroup instanceof LinkGroup) {
            $query->andWhere("l.group = :linkgroup")->setParameter("linkgroup", $linkgroup);
            $query->setParameter("newestExpirationTime", new \DateTime('-7 days'));
        } else {
            $query->setParameter("newestExpirationTime", new \DateTime('-3 days'));
        }

        $result = $query->setFirstResult(($page - 1) * $linksPerPage)->setMaxResults($linksPerPage);
        $links = new Paginator($result, $fetchJoinCollection = true);

        $resultQuery = $this->findLinks($links->getQuery()->getResult())
            ->addSelect("(l.totalUpvotes - l.totalDownvotes) as HIDDEN _total_votes")
            ->addOrderBy("_total_votes", "DESC");

        return [
            'links' => $resultQuery->getQuery()->getResult(),
            'linksNumber' => count($links),
        ];
    }

    /**
     * Returns all added links from given group
     *
     * @param $linkgroup
     * @param $page
     * @param $linksPerPage
     */
    public function findAllGroupLinks($linkgroup, $page, $linksPerPage)
    {
        $qb = $this->createQueryBuilder("l");

        $query = $qb
            ->select("l, g, c, g, u, d, uv, dv")
            ->join("l.group", "g")
            ->leftjoin("l.comments", "c")
            ->leftjoin("l.user", "u")
            ->leftjoin("l.domain", "d")
            ->leftjoin("l.upvotes", "uv")
            ->leftjoin("l.downvotes", "dv")
            ->orderBy('l.id', 'DESC')
            ->where("(l.totalUpvotes - l.totalDownvotes) > -5")
            ->andWhere("l.group = :linkgroup")->setParameter("linkgroup", $linkgroup)
        ;

        $result = $query->setFirstResult(($page - 1) * $linksPerPage)
            ->setMaxResults($linksPerPage);
        $links = new Paginator($result, $fetchJoinCollection = true);

        return [
            'links' => $links,
            'linksNumber' => count($links),
        ];

    }

    /**
     * Highest rated links
     *
     * @param $days
     * @return mixed
     */
    public function findBestRated($days)
    {
        $qb = $this->createQueryBuilder("l");

        $query = $qb
            ->select("l, c, u, uv, dv, g")
            ->addSelect("(l.totalUpvotes - l.totalDownvotes) as HIDDEN total_votes")
            ->join("l.group", "g")
            ->leftjoin("l.comments", "c")
            ->join("l.user", "u")
            ->leftjoin("l.upvotes", "uv")
            ->leftjoin("l.downvotes", "dv")
            // ->orderBy("(l.totalUpvotes - l.totalDownvotes)", "DESC")
            ->orderBy("total_votes", "DESC")
            ->where("l.added > :dateFrom")->setParameter("dateFrom", new \DateTime(-$days . " days"))
            ->groupBy("l.id")
            ->setMaxResults(5);

        return $query->getQuery()->getResult();
    }
}
