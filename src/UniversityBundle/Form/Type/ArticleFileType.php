<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleFileType extends BaseAbstractType
{
    const name = 'uni_article';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile','file')
        ;
    }

    public function getName()
    {
        return self::name;
    }
}