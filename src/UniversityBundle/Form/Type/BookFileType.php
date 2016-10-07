<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class BookFileType  extends BaseAbstractType
{
    const name = 'uni_book';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile',FileType::class)
            ->add('documentFile',FileType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return self::name;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UniversityBundle\Entity\Book'
        ));
    }
}