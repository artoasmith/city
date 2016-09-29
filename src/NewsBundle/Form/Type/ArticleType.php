<?php

namespace NewsBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title','text')
            ->add('pictureFile','file')
            ->add('date','date',array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
            ))
            ->add('tags','collection',['type'=>'text','allow_add'=>true])
            ->add('text','text')
            ->add('sections','collection',['type'=>'integer','allow_add'=>true])
        ;
    }

    public function getName()
    {
        return 'article';
    }

}