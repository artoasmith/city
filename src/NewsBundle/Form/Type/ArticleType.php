<?php

namespace NewsBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use UniversityBundle\Entity\ArticleSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ArticleType extends AbstractType
{
    const NAME = 'newsArticle';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', HiddenType::class)
            ->add('author', HiddenType::class)
            ->add('pictureFile',FileType::class)
            ->add('files',CollectionType::class,['entry_type'=>FileType::class,'allow_add'=>true])
            ->add('date',DateType::class,array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
            ))
            ->add('tags',CollectionType::class,['entry_type'=>HiddenType::class,'allow_add'=>true])
            ->add('text',HiddenType::class)
            ->add('sections',CollectionType::class,['entry_type'=>IntegerType::class,'allow_add'=>true])
            ->add('description', HiddenType::class)
            ->add('metaTitle', HiddenType::class)
            ->add('metaDescription', HiddenType::class)
            ->add('metaKeyWords', HiddenType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return self::NAME;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NewsBundle\Entity\Article'
        ));
    }
}