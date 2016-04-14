<?php

namespace AppBundle\Form;

use AppBundle\Form\Transformer\LinkGroupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('title', TextType::class, array(
                'required' => true,
            ))
            ->add('description', TextAreaType::class, array(
                'attr' => array(
                    'rows' => 2,
                ),
                'required' => false,
            ))
            /*->add('group', EntityType::class, array(
        'class' => 'AppBundle:LinkGroup',
        ))*/
            ->add('group', TextType::class, array(
                'attr' => array('class' => 'group-autocomplete'),
            ))
            ->add('adult', CheckboxType::class, array(
                'label' => 'link.adult_content',
                'required' => false,
            ))

            ->add('thumbnail', HiddenType::class)
        ;

        $builder->get('group')->addModelTransformer(new LinkGroupTransformer($options['em']));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Link',
            'em' => null,
        ));
    }
}
