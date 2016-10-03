<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class ArticleSectionType extends BaseAbstractType
{
    const name = 'uni_article_section';
    const names = 'uni_article_sections';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('parentSection','entity',['class' => 'UniversityBundle:ArticleSection', 'empty_data' => null])
        ;
    }

    public function getName()
    {
        return self::name;
    }

}