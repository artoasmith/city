<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class BookSectionType extends BaseAbstractType
{
    const name = 'uni_book_section';
    const names = 'uni_book_sections';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',HiddenType::class)
            ->add('parentSection',EntityType::class,['class' => 'UniversityBundle:BookSection', 'empty_data' => null])
        ;
    }

    public function getBlockPrefix()
    {
        return self::name;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UniversityBundle\Entity\BookSection'
        ));
    }
}