<?php
// src/AppBundle/Entity/User.php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Link", mappedBy="user")
     */
    protected $links;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\LinkGroup", mappedBy="owner")
     */
    protected $linkGroups;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="user")
     */
    protected $comments;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Vote", mappedBy="user")
     */
    private $votes;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Entry", mappedBy="user")
     */
    private $entries;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\LinkGroup", mappedBy="moderators")
     */
    private $moderatedGroups;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="user")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $notifications;

    /**
     * @Assert\Image(maxSize="6000000")
     */
    private $file;

    /**
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        if (null !== $this->file) {
            $this->avatar = $this->username . '.' . $this->file->guessExtension();
            $this->file->move($this->getUploadRootDir(), $this->avatar);
            $this->file = null;
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'avatars';
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return null === $this->avatar
        ? null
        : $this->getUploadDir() . '/' . $this->avatar;
    }

    /**
     * Add link
     *
     * @param \AppBundle\Entity\Link $link
     *
     * @return User
     */
    public function addLink(\AppBundle\Entity\Link $link)
    {
        $link->setUser($this);
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
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return User
     */
    public function addComment(\AppBundle\Entity\Comment $comment)
    {
        $comment->setUser($this);
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
     * Add vote
     *
     * @param \AppBundle\Entity\Vote $vote
     *
     * @return User
     */
    public function addVote(\AppBundle\Entity\Vote $vote)
    {
        $vote->setUser($this);
        $this->votes[] = $vote;

        return $this;
    }

    /**
     * Remove vote
     *
     * @param \AppBundle\Entity\Vote $vote
     */
    public function removeVote(\AppBundle\Entity\Vote $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Add linkGroup
     *
     * @param \AppBundle\Entity\LinkGroup $linkGroup
     *
     * @return User
     */
    public function addLinkGroup(\AppBundle\Entity\LinkGroup $linkGroup)
    {
        $linkGroups->setUser($this);
        $this->linkGroups[] = $linkGroup;

        return $this;
    }

    /**
     * Remove linkGroup
     *
     * @param \AppBundle\Entity\LinkGroup $linkGroup
     */
    public function removeLinkGroup(\AppBundle\Entity\LinkGroup $linkGroup)
    {
        $this->linkGroups->removeElement($linkGroup);
    }

    /**
     * Get linkGroups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLinkGroups()
    {
        return $this->linkGroups;
    }

    /**
     * Add entry
     *
     * @param \AppBundle\Entity\Entry $entry
     *
     * @return User
     */
    public function addEntry(\AppBundle\Entity\Entry $entry)
    {
        $entry->setUser($this);
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
     * Add moderatedGroup
     *
     * @param \AppBundle\Entity\LinkGroup $moderatedGroup
     *
     * @return User
     */
    public function addModeratedGroup(\AppBundle\Entity\LinkGroup $moderatedGroup)
    {
        $this->moderatedGroups[] = $moderatedGroup;

        return $this;
    }

    /**
     * Remove moderatedGroup
     *
     * @param \AppBundle\Entity\LinkGroup $moderatedGroup
     */
    public function removeModeratedGroup(\AppBundle\Entity\LinkGroup $moderatedGroup)
    {
        $this->moderatedGroups->removeElement($moderatedGroup);
    }

    /**
     * Get moderatedGroups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModeratedGroups()
    {
        return $this->moderatedGroups;
    }

    /**
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return User
     */
    public function addNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \AppBundle\Entity\Notification $notification
     */
    public function removeNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    public function getUnreadNotificationsNumber()
    {
        $notificationsNumber = 0;
        foreach ($this->notifications as $notification) {
            if ($notification->getUnread()) {
                $notificationsNumber++;
            }
        }
        return $notificationsNumber;
    }
}
