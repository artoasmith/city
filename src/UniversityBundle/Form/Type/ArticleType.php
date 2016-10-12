<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class ArticleType  extends AbstractType
{
    const NAME = 'uniArticle';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile',FileType::class)
            ->add('author',HiddenType::class)
            ->add('date',DateType::class,array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
            ))
            ->add('text',HiddenType::class)
            ->add('tags',CollectionType::class,['entry_type'=>HiddenType::class,'allow_add'=>true])
            ->add('title',HiddenType::class)
            ->add('sections',CollectionType::class,['entry_type' =>IntegerType::class, 'allow_add' => true])
        ;
    }

    public function getBlockPrefix()
    {
        return self::NAME;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UniversityBundle\Entity\Article'
        ));
    }
}