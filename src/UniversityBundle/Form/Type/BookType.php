<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BookType  extends BaseAbstractType
{
    const name = 'uni_book';
    const names = 'uni_books';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title','text')
            ->add('author','text')
            ->add('description','text')
            ->add('tags','collection',['type'=>'text','allow_add'=>true])
            ->add('sections','collection',['type' => 'integer', 'allow_add' => true])
            ->add('pictureFile','file')
            ->add('documentFile','file')
        ;
    }

    public function getName()
    {
        return self::name;
    }
}