<?php

namespace AppBundle\Form;

use AppBundle\Form\Transformer\LinkGroupModeratorTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkGroupEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, array(
                'required' => false,
            ))
            ->add('moderators', CollectionType::class, array(
                'entry_type' => TextType::class,
                'entry_options' => array(
                    'attr' => array('class' => 'user-autocompleter'),
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ))
        ;
        $builder->get('moderators')->addModelTransformer(new LinkGroupModeratorTransformer($options['em']));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\LinkGroup',
            'em' => null,
        ));
    }
}
