<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Link
 *
 * @ORM\Table(name="link")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LinkRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Link extends AbstractUniqueContent
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
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="links", cascade={"persist"})
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id", nullable=false)
     */
    private $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="links")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="link", cascade={"all"})
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="LinkGroup", inversedBy="links")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     */
    private $group;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     */
    private $thumbnail;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"}, inversedBy="linkUpvotes")
     * @ORM\JoinTable(name="link_upvotes")
     * @ORM\OrderBy({"id": "DESC"})
     */
    private $upvotes;

    /**
     * @ORM\ManyToMany(targetEntity="Vote", cascade={"all"}, inversedBy="linkDownvotes")
     * @ORM\JoinTable(name="link_downvotes")
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
     * @ORM\Column(name="mainpage_at", type="datetime", nullable=true)
     */
    private $mainpageAt;

    /**
     * @ORM\Column(name="adult", type="boolean", nullable=true)
     */
    private $adult;

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
     * @return Link
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
     * @return Link
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Link
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
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Link
     */
    public function addComment(\AppBundle\Entity\Comment $comment)
    {
        $comment->setLink($this);
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comment $comment
     */
    public function removeComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\LinkGroup $group
     *
     * @return Link
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
        return $this->group;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Link
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Link
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
     * Set description
     *
     * @param string $description
     *
     * @return Link
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     *
     * @return Link
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
     * Set domain
     *
     * @param \AppBundle\Entity\Domain $domain
     *
     * @return Link
     */
    public function setDomain(\AppBundle\Entity\Domain $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return \AppBundle\Entity\Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Add upvote
     *
     * @param \AppBundle\Entity\Vote $upvote
     *
     * @return Link
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
     * @return Link
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
     * @return Link
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
     * @return Link
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
     * Set mainpageAt
     *
     * @param \DateTime $mainpageAt
     *
     * @return Link
     */
    public function setMainpageAt($mainpageAt)
    {
        $this->mainpageAt = $mainpageAt;

        return $this;
    }

    /**
     * Get mainpageAt
     *
     * @return \DateTime
     */
    public function getMainpageAt()
    {
        return $this->mainpageAt;
    }

    /**
     * Set adult
     *
     * @param boolean $adult
     *
     * @return Link
     */
    public function setAdult($adult)
    {
        $this->adult = $adult;

        return $this;
    }

    /**
     * Get adult
     *
     * @return boolean
     */
    public function getAdult()
    {
        return $this->adult;
    }
}
