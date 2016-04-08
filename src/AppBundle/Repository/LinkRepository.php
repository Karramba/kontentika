<?php

namespace AppBundle\Repository;

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
        $this->prepareQuery();
    }

    private function prepareQuery()
    {
        $qb = $this->createQueryBuilder("l");
        $this->query = $qb
            ->select("l, c, uv, dv, g, u, d")
        // ->addSelect("(l.totalUpvotes - l.totalDownvotes) as HIDDEN _total_votes")
            ->leftjoin("l.comments", "c")
            ->leftjoin("l.group", "g")
            ->leftjoin("l.user", "u")
            ->leftjoin("l.domain", "d")
            ->leftjoin("l.upvotes", "uv")
            ->leftjoin("l.downvotes", "dv")
            ->orderBy('l.id', 'DESC')
            ->where("(l.totalUpvotes - l.totalDownvotes) > -5")
        ;
    }

    /**
     * Result of built query
     *
     * @param $page
     * @param $linksPerPage
     */
    private function getResult($page, $linksPerPage)
    {
        $result = $this->query->setFirstResult(($page - 1) * $linksPerPage)
            ->setMaxResults($linksPerPage);

        $links = new Paginator($result, $fetchJoinCollection = true);

        return [
            'links' => $links,
            'linksNumber' => count($links),
        ];
    }

    /**
     * @param $user
     * @param $page
     * @param $linksPerPage
     * @return mixed
     */
    public function findNewestLinks($user, $page, $linksPerPage)
    {
        $this->query->andWhere("l.mainpageAt is null");

        return $this->getResult($page, $linksPerPage);
    }

    /**
     * Returns only links that upvotes exceeded parameters.yml mainpage_entry_votes value
     *
     * @param $user
     * @param $page
     * @param $linksPerPage
     * @return mixed
     */
    public function findBestLinks($user, $page, $linksPerPage)
    {
        $this->query->andWhere("(l.totalUpvotes - l.totalDownvotes) > 0");
        $this->query->andWhere("l.mainpageAt is not null");
        $this->query->orderBy("l.mainpageAt", "DESC");

        return $this->getResult($page, $linksPerPage);
    }

    /**
     * @param $user
     * @param $page
     * @param $linksPerPage
     * @return mixed
     */
    public function findRisingLinks($user, $page, $linksPerPage)
    {
        $this->query->addSelect("(l.totalUpvotes - l.totalDownvotes) as HIDDEN _total_votes");
        $this->query->andWhere("l.mainpageAt is null");
        $this->query->orderBy("_total_votes", "DESC");

        return $this->getResult($page, $linksPerPage);
    }

    /**
     * Returns all added links from given group
     *
     * @param $linkGroup
     * @param $page
     * @param $linksPerPage
     */
    public function findAllGroupLinks($linkGroup, $page, $linksPerPage)
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
            ->andWhere("l.group = :linkGroup")->setParameter("linkGroup", $linkGroup)
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
