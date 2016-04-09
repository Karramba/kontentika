<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entry
 *
 * @ORM\Table(name="entry")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EntryRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Entry extends AbstractUniqueContent
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
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="entries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="LinkGroup", inversedBy="entries")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Entry", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"}, inversedBy="entryUpvotes")
     * @ORM\JoinTable(name="entry_upvotes")
     */
    private $upvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"}, inversedBy="entryDownvotes")
     * @ORM\JoinTable(name="entry_downvotes")
     */
    private $downvotes;

    /**
     * @ORM\Column(name="total_upvotes", type="integer", options={"default"=0}, nullable=false)
     */
    private $totalUpvotes;

    /**
     * @ORM\Column(name="total_downvotes", type="integer", options={"default"=0}, nullable=false)
     */
    private $totalDownvotes;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="change", field={"content"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    public function __construct()
    {
        parent::__construct();
        $this->totalUpvotes = 0;
        $this->totalDownvotes = 0;
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
     * Set content
     *
     * @param string $content
     *
     * @return Entry
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Entry
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
     * Set group
     *
     * @param \AppBundle\Entity\LinkGroup $group
     *
     * @return Entry
     */
    public function setGroup(\AppBundle\Entity\LinkGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\LinkGroup
     */
    public function getGroup()
    {
        if (!$this->group && $this->getFirstParent() instanceof Entry) {
            return $this->getFirstParent()->getGroup();
        }
        return $this->group;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Entry
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set totalUpvotes
     *
     * @param integer $totalUpvotes
     *
     * @return Entry
     */
    public function setTotalUpvotes($totalUpvotes)
    {
        $this->totalUpvotes = $totalUpvotes;

        return $this;
    }

    /**
     * Get totalUpvotes
     *
     * @return integer
     */
    public function getTotalUpvotes()
    {
        return $this->totalUpvotes;
    }

    /**
     * Set totalDownvotes
     *
     * @param integer $totalDownvotes
     *
     * @return Entry
     */
    public function setTotalDownvotes($totalDownvotes)
    {
        $this->totalDownvotes = $totalDownvotes;

        return $this;
    }

    /**
     * Get totalDownvotes
     *
     * @return integer
     */
    public function getTotalDownvotes()
    {
        return $this->totalDownvotes;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Entry $parent
     *
     * @return Entry
     */
    public function setParent(\AppBundle\Entity\Entry $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Entry
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getFirstParent()
    {
        if ($this->parent != null && $this->parent->getParent() != null) {
            return $this->parent->getFirstParent();
        } else {
            return $this->parent;
        }
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\Entry $child
     *
     * @return Entry
     */
    public function addChild(\AppBundle\Entity\Entry $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Entry $child
     */
    public function removeChild(\AppBundle\Entity\Entry $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add upvote
     *
     * @param \AppBundle\Entity\Vote $upvote
     *
     * @return Entry
     */
    public function addUpvote(\AppBundle\Entity\Vote $upvote)
    {
        $this->upvotes[] = $upvote;
        $this->totalUpvotes = $this->upvotes->count();

        return $this;
    }

    /**
     * Remove upvote
     *
     * @param \AppBundle\Entity\Vote $upvote
     */
    public function removeUpvote(\AppBundle\Entity\Vote $upvote)
    {
        $this->upvotes->removeElement($upvote);
        $this->totalUpvotes = $this->upvotes->count();
    }

    /**
     * Get upvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUpvotes()
    {
        return $this->upvotes;
    }

    /**
     * Add downvote
     *
     * @param \AppBundle\Entity\Vote $downvote
     *
     * @return Entry
     */
    public function addDownvote(\AppBundle\Entity\Vote $downvote)
    {
        $this->downvotes[] = $downvote;
        $this->totalDownvotes = $this->downvotes->count();

        return $this;
    }

    /**
     * Remove downvote
     *
     * @param \AppBundle\Entity\Vote $downvote
     */
    public function removeDownvote(\AppBundle\Entity\Vote $downvote)
    {
        $this->downvotes->removeElement($downvote);
        $this->totalDownvotes = $this->downvotes->count();
    }

    /**
     * Get downvotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDownvotes()
    {
        return $this->downvotes;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Entry
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
