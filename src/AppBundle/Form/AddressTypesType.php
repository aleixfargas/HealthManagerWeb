<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddressTypesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', ChoiceType::class, array(
                'choices'=>$options['names']
            )
        );
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class' => 'AppBundle\Entity\AddressTypes',
                    'names' => array()
                )
            )
            ->setRequired('names')
            ->setAllowedTypes('names', array('array'));
        
    }
}
