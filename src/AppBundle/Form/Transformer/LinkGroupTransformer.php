<?php

namespace AppBundle\Form\Transformer;

use AppBundle\Entity\LinkGroup;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LinkGroupTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($linkGroup)
    {
        if ($linkGroup instanceof LinkGroup) {
            return $linkGroup->getTitle();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($groupTitle)
    {
        if (!$groupTitle) {
            return;
        }
        $linkGroup = $this->manager->getRepository('AppBundle:LinkGroup')->findOneByTitle($groupTitle);

        if (null === $linkGroup) {
            throw new TransformationFailedException(sprintf('An issue with number "%s" does not exist!', $groupTitle));
        }
        return $linkGroup;
    }
}
