<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BookFileType  extends BaseAbstractType
{
    const name = 'uni_book';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile','file')
            ->add('documentFile','file')
        ;
    }

    public function getName()
    {
        return self::name;
    }
}