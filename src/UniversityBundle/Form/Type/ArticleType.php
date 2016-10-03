<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleType  extends BaseAbstractType
{
    const name = 'uni_article';
    const names = 'uni_articles';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile','file')
            ->add('author','text')
            ->add('date','date',array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
            ))
            ->add('text','text')
            ->add('tags','collection',['type'=>'text','allow_add'=>true])
            ->add('title','text')
            ->add('sections','collection',['type' => 'integer', 'allow_add' => true])
        ;
    }

    public function getName()
    {
        return self::name;
    }
}