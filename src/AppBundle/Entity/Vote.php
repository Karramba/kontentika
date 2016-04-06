<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vote
 *
 * @ORM\Table(name="vote")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoteRepository")
 */
class Vote
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="voted", type="datetime")
     */
    private $voted;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="votes")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Link", mappedBy="upvotes")
     */
    private $linkUpvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Link", mappedBy="downvotes")
     */
    private $linkDownvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Entry", mappedBy="upvotes")
     */
    private $entryUpvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Entry", mappedBy="downvotes")
     */
    private $entryDownvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Comment", mappedBy="upvotes")
     */
    private $commentUpvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Comment", mappedBy="downvotes")
     */
    private $commentDownvotes;

    public function __construct()
    {
        $this->voted = new \DateTime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set voted
     *
     * @param \DateTime $voted
     *
     * @return Vote
     */
    public function setVoted($voted)
    {
        $this->voted = $voted;

        return $this;
    }

    /**
     * Get voted
     *
     * @return \DateTime
     */
    public function getVoted()
    {
        return $this->voted;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Vote
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Vote
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add linkUpvote
     *
     * @param \AppBundle\Entity\Link $linkUpvote
     *
     * @return Vote
     */
    public function addLinkUpvote(\AppBundle\Entity\Link $linkUpvote)
    {
        $this->linkUpvotes[] = $linkUpvote;

        return $this;
    }

    /**
     * Remove linkUpvote
     *
     * @param \AppBundle\Entity\Link $linkUpvote
     */
    public function removeLinkUpvote(\AppBundle\Entity\Link $linkUpvote)
    {
        $this->linkUpvotes->removeElement($linkUpvote);
    }

    /**
     * Get linkUpvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLinkUpvotes()
    {
        return $this->linkUpvotes;
    }

    /**
     * Add linkDownvote
     *
     * @param \AppBundle\Entity\Link $linkDownvote
     *
     * @return Vote
     */
    public function addLinkDownvote(\AppBundle\Entity\Link $linkDownvote)
    {
        $this->linkDownvotes[] = $linkDownvote;

        return $this;
    }

    /**
     * Remove linkDownvote
     *
     * @param \AppBundle\Entity\Link $linkDownvote
     */
    public function removeLinkDownvote(\AppBundle\Entity\Link $linkDownvote)
    {
        $this->linkDownvotes->removeElement($linkDownvote);
    }

    /**
     * Get linkDownvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLinkDownvotes()
    {
        return $this->linkDownvotes;
    }

    /**
     * Add entryUpvote
     *
     * @param \AppBundle\Entity\Entry $entryUpvote
     *
     * @return Vote
     */
    public function addEntryUpvote(\AppBundle\Entity\Entry $entryUpvote)
    {
        $this->entryUpvotes[] = $entryUpvote;

        return $this;
    }

    /**
     * Remove entryUpvote
     *
     * @param \AppBundle\Entity\Entry $entryUpvote
     */
    public function removeEntryUpvote(\AppBundle\Entity\Entry $entryUpvote)
    {
        $this->entryUpvotes->removeElement($entryUpvote);
    }

    /**
     * Get entryUpvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntryUpvotes()
    {
        return $this->entryUpvotes;
    }

    /**
     * Add entryDownvote
     *
     * @param \AppBundle\Entity\Entry $entryDownvote
     *
     * @return Vote
     */
    public function addEntryDownvote(\AppBundle\Entity\Entry $entryDownvote)
    {
        $this->entryDownvotes[] = $entryDownvote;

        return $this;
    }

    /**
     * Remove entryDownvote
     *
     * @param \AppBundle\Entity\Entry $entryDownvote
     */
    public function removeEntryDownvote(\AppBundle\Entity\Entry $entryDownvote)
    {
        $this->entryDownvotes->removeElement($entryDownvote);
    }

    /**
     * Get entryDownvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntryDownvotes()
    {
        return $this->entryDownvotes;
    }

    /**
     * Add commentUpvote
     *
     * @param \AppBundle\Entity\Comment $commentUpvote
     *
     * @return Vote
     */
    public function addCommentUpvote(\AppBundle\Entity\Comment $commentUpvote)
    {
        $this->commentUpvotes[] = $commentUpvote;

        return $this;
    }

    /**
     * Remove commentUpvote
     *
     * @param \AppBundle\Entity\Comment $commentUpvote
     */
    public function removeCommentUpvote(\AppBundle\Entity\Comment $commentUpvote)
    {
        $this->commentUpvotes->removeElement($commentUpvote);
    }

    /**
     * Get commentUpvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentUpvotes()
    {
        return $this->commentUpvotes;
    }

    /**
     * Add commentDownvote
     *
     * @param \AppBundle\Entity\Comment $commentDownvote
     *
     * @return Vote
     */
    public function addCommentDownvote(\AppBundle\Entity\Comment $commentDownvote)
    {
        $this->commentDownvotes[] = $commentDownvote;

        return $this;
    }

    /**
     * Remove commentDownvote
     *
     * @param \AppBundle\Entity\Comment $commentDownvote
     */
    public function removeCommentDownvote(\AppBundle\Entity\Comment $commentDownvote)
    {
        $this->commentDownvotes->removeElement($commentDownvote);
    }

    /**
     * Get commentDownvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentDownvotes()
    {
        return $this->commentDownvotes;
    }
}
