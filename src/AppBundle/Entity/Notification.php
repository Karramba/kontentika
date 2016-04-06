<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationRepository")
 */
class Notification extends AbstractUniqueContent
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
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(name="message", type="string", length=255)
     */
    private $message;

    /**
     * @ORM\Column(name="unread", type="boolean", options={"default": 1})
     */
    private $unread = 1;

    /**
     * @ORM\Column(name="content_type", type="string", length=255)
     */
    private $contentType;

    /**
     * @ORM\Column(name="content_unique_id", type="string", length=16)
     */
    private $contentUniqueId;

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
     * Set message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Notification
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
     * Set unread
     *
     * @param boolean $unread
     *
     * @return Notification
     */
    public function setUnread($unread)
    {
        $this->unread = $unread;

        return $this;
    }

    /**
     * Get unread
     *
     * @return boolean
     */
    public function getUnread()
    {
        return $this->unread;
    }

    /**
     * Set contentType
     *
     * @param string $contentType
     *
     * @return Notification
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set contentUniqueId
     *
     * @param string $contentUniqueId
     *
     * @return Notification
     */
    public function setContentUniqueId($contentUniqueId)
    {
        $this->contentUniqueId = $contentUniqueId;

        return $this;
    }

    /**
     * Get contentUniqueId
     *
     * @return string
     */
    public function getContentUniqueId()
    {
        return $this->contentUniqueId;
    }
}
