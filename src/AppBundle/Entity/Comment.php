<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommentRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Comment extends AbstractUniqueContent
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
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Link", inversedBy="comments")
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id")
     */
    private $link;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"}, inversedBy="commentUpvotes")
     * @ORM\JoinTable(name="comment_upvotes")
     */
    private $upvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"}, inversedBy="commentDownvotes")
     * @ORM\JoinTable(name="comment_downvotes")
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
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent")
     */
    private $children;

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
     * @return Comment
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
     * @return Comment
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
     * Set link
     *
     * @param \AppBundle\Entity\Link $link
     *
     * @return Comment
     */
    public function setLink(\AppBundle\Entity\Link $link = null)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return \AppBundle\Entity\Link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Comment
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
     * Add upvote
     *
     * @param \AppBundle\Entity\Vote $upvote
     *
     * @return Comment
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
     * @return Comment
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
     * Set totalUpvotes
     *
     * @param integer $totalUpvotes
     *
     * @return Comment
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
     * @return Comment
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
     * @param \AppBundle\Entity\Comment $parent
     *
     * @return Comment
     */
    public function setParent(\AppBundle\Entity\Comment $parent = null)
    {
        $this->link = $parent->getLink();
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Comment
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
     * @param \AppBundle\Entity\Comment $child
     *
     * @return Comment
     */
    public function addChild(\AppBundle\Entity\Comment $child)
    {
        $child->setParent($this);
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Comment $child
     */
    public function removeChild(\AppBundle\Entity\Comment $child)
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Comment
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
