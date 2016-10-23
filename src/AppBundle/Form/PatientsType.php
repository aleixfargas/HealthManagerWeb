<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PatientsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dni')
            ->add('name')
            ->add('surname')
            ->add('age')
            ->add('birthday')
            ->add('job')
            ->add('photo')
            ->add('addresses')
            ->add('telephones')
            ->add('emails')
            ->add('diseases')
            ->add('operations')
            ->add('allergies')
            ->add('registerDate')
            ->add('notes')
            ->add('save', SubmitType::class, array('label' => 'Create Patient'))
        ;
    }
}
