<?php

namespace AppBundle\Form;

use AppBundle\Form\Transformer\LinkGroupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, array(
                'attr' => array('rows' => $options['rows']),
            ))
            ->add('group', TextType::class, array(
                'attr' => array('class' => 'group-autocomplete'),
                'invalid_message' => 'linkgroup.group_invalid_or_locked',
            ))
        ;
        $builder->get('group')->addModelTransformer(new LinkGroupTransformer($options['em']));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Entry',
            'em' => null,
            'rows' => 5,
        ));
    }
}
