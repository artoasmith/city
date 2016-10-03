<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 29.09.16
 * Time: 10:29
 */

namespace CommentsBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CommentType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parentComment','entity',['class' => 'CommentsBundle:Comment', 'empty_data' => null])
            ->add('text','text')

            ->add('newsArticle','entity',['class' => 'NewsBundle:Article', 'empty_data' => null])
            ->add('uniEvent','entity',['class' => 'UniversityBundle:Event', 'empty_data' => null])
        ;
    }

    public function getName()
    {
        return 'comment';
    }
}