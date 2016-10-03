<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EventSectionType extends BaseAbstractType
{
    const name = 'uni_event_section';
    const names = 'uni_event_sections';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('parentSection','entity',['class' => 'UniversityBundle:EventSection', 'empty_data' => null])
        ;
    }

    public function getName()
    {
        return self::name;
    }
}
