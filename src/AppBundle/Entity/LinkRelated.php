<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LinkRelated
 *
 * @ORM\Table(name="link_related")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LinkRelatedRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class LinkRelated extends AbstractUniqueContent
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
     * @ORM\Column(name="url", type="string", length=255)
     * @Assert\Url()
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     */
    private $thumbnail;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"})
     * @ORM\JoinTable(name="link_related_upvotes")
     * @ORM\OrderBy({"id": "DESC"})
     */
    private $upvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"})
     * @ORM\JoinTable(name="link_related_downvotes")
     * @ORM\OrderBy({"id": "DESC"})
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
     * @ORM\ManyToOne(targetEntity="Link", inversedBy="related")
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id")
     */
    private $link;

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
     * Set url
     *
     * @param string $url
     *
     * @return LinkRelated
     */
    public function setUrl($url)
    {
        if (!strstr($url, 'http')) {
            $this->url = 'http://' . $url;
        } else {
            $this->url = $url;
        }
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return LinkRelated
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return LinkRelated
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
     * Set thumbnail
     *
     * @param string $thumbnail
     *
     * @return LinkRelated
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getThumbnailAbsolutePath()
    {
        return null === $this->thumbnail
        ? null
        : $this->getUploadRootDir() . '/' . $this->thumbnail;
    }

    public function getThumbnailWebPath()
    {
        return null === $this->thumbnail
        ? null
        : $this->getUploadDir() . '/' . $this->thumbnail;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory thumbnail where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads';
    }

    /**
     * Set totalUpvotes
     *
     * @param integer $totalUpvotes
     *
     * @return LinkRelated
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
     * @return LinkRelated
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return LinkRelated
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
     * Add upvote
     *
     * @param \AppBundle\Entity\Vote $upvote
     *
     * @return LinkRelated
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
     * @return LinkRelated
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
     * Set link
     *
     * @param \AppBundle\Entity\Link $link
     *
     * @return LinkRelated
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
}
