<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * UserGroupRepository
 */
class LinkGroupRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * List all groups - paginated
     *
     * @param $page
     * @param $linksPerPage
     * @return array
     */
    public function findAllGroups($page, $linksPerPage)
    {
        $qb = $this->createQueryBuilder("lg");
        $query = $qb
            ->select("lg, o, m")
            ->leftjoin("lg.owner", "o")
            ->leftjoin("lg.moderators", "m")
            ->orderBy('lg.id', 'DESC')
        ;

        $result = $query->setFirstResult(($page - 1) * $linksPerPage)
            ->setMaxResults($linksPerPage);

        $linkGroups = new Paginator($result, $fetchJoinCollection = true);

        return [
            'linkGroups' => $linkGroups->getQuery()->getResult(),
            'linkGroupsNumber' => count($linkGroups),
        ];
    }

    /**
     * List of groups that matches given linkgroup name
     *
     * @param $title
     * @return array
     */
    public function findForAutocompleter($title)
    {
        $groups = $this->createQueryBuilder("lg")
            ->select("lg.title, count(l) AS HIDDEN total_links ")
            ->leftJoin("lg.links", "l")
            ->where("lg.title LIKE :title")->setParameter("title", "%" . $title . "%")
            ->groupBy("lg.id")
            ->orderBy("total_links", "DESC")
            ->setMaxResults(20)
            ->getQuery()->getArrayResult();

        return $groups;
    }
}
