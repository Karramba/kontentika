<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AbstractUniqueContent
{
    public function __construct()
    {
        $this->uniqueId = hash('crc32b', uniqid());
        $this->added = new \DateTime();
    }

    /**
     * @ORM\Column(name="unique_id", type="string", unique=true, length=16)
     */
    private $uniqueId;

    /**
     * @ORM\Column(name="added", type="datetime")
     */
    private $added;

    /**
     * @ORM\Column(name="ip", type="string", length=15)
     * @Assert\Ip
     */
    private $ip;

    public function __toString()
    {
        return $this->uniqueId;
    }

    /**
     * Set uniqueId
     *
     * @param integer $uniqueId
     *
     * @return AbstractUniqueContent
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    /**
     * Get uniqueId
     *
     * @return integer
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * Set added
     *
     * @param \DateTime $added
     *
     * @return AbstractUniqueContent
     */
    public function setAdded($added)
    {
        $this->added = $added;

        return $this;
    }

    /**
     * Get added
     *
     * @return \DateTime
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return AbstractUniqueContent
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
}
