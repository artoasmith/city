<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EventType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title','text')
            ->add('pictureFile','file')
            ->add('description','text')
            ->add('date','date',array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
            ))
            ->add('tags','collection',['type'=>'text','allow_add'=>true])
            ->add('duration','number')
            ->add('sections','collection',['type' => 'integer', 'allow_add' => true])
        ;
    }

    public function getName()
    {
        return 'uni_event';
    }

}