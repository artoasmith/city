<?php

namespace NewsBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SectionType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('parentSection','entity',['class' => 'NewsBundle:Section', 'empty_data' => null])
        ;
    }

    public function getName()
    {
        return 'section';
    }
}
