<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BookSectionType extends BaseAbstractType
{
    const name = 'uni_book_section';
    const names = 'uni_book_sections';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('parentSection','entity',['class' => 'UniversityBundle:BookSection', 'empty_data' => null])
        ;
    }

    public function getName()
    {
        return self::name;
    }
}