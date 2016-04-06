<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserGroup
 *
 * @ORM\Table(name="link_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LinkGroupRepository")
 * @UniqueEntity("title")
 */
class LinkGroup extends AbstractUniqueContent
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
     * @ORM\Column(name="title", type="string", length=32, unique=true)
     * @Assert\Regex(
     *     pattern="/^[a-ż0-9+\_]+$/i",
     *     match=true,
     *     message="linkgroup.name_invalid"
     * )
     * @Assert\Length(min=3, max=32, minMessage="linkgroup.name_too_short", maxMessage="linkgroup.name_too_long")
     * @Assert\NotEqualTo(value="new", message="linkgroup.name_restricted")
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="linkGroups")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="Link", mappedBy="group")
     */
    private $links;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", unique=false, length=128, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Entry", mappedBy="group")
     */
    private $entries;

    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User", inversedBy="moderatedGroups")
     * @ORM\JoinTable(name="linkgroup_moderators")
     */
    private $moderators;

    public function __construct()
    {
        parent::__construct();
    }

    public function __toString()
    {
        return $this->title;
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
     * Set title
     *
     * @param string $title
     *
     * @return UserGroup
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
     * Set owner
     *
     * @param \UserBundle\Entity\User $owner
     *
     * @return LinkGroup
     */
    public function setOwner(\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add link
     *
     * @param \AppBundle\Entity\Link $link
     *
     * @return LinkGroup
     */
    public function addLink(\AppBundle\Entity\Link $link)
    {
        $this->links[] = $link;

        return $this;
    }

    /**
     * Remove link
     *
     * @param \AppBundle\Entity\Link $link
     */
    public function removeLink(\AppBundle\Entity\Link $link)
    {
        $this->links->removeElement($link);
    }

    /**
     * Get links
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return LinkGroup
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
     * Add entry
     *
     * @param \AppBundle\Entity\Entry $entry
     *
     * @return LinkGroup
     */
    public function addEntry(\AppBundle\Entity\Entry $entry)
    {
        $entry->setGroup($this);
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Remove entry
     *
     * @param \AppBundle\Entity\Entry $entry
     */
    public function removeEntry(\AppBundle\Entity\Entry $entry)
    {
        $this->entries->removeElement($entry);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Add moderator
     *
     * @param \UserBundle\Entity\User $moderator
     *
     * @return LinkGroup
     */
    public function addModerator(\UserBundle\Entity\User $moderator)
    {
        $this->moderators[] = $moderator;

        return $this;
    }

    /**
     * Remove moderator
     *
     * @param \UserBundle\Entity\User $moderator
     */
    public function removeModerator(\UserBundle\Entity\User $moderator)
    {
        $this->moderators->removeElement($moderator);
    }

    /**
     * Get moderators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModerators()
    {
        return $this->moderators;
    }
}
