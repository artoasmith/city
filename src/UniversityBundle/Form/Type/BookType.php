<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType  extends BaseAbstractType
{
    const name = 'uni_book';
    const names = 'uni_books';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',HiddenType::class)
            ->add('author',HiddenType::class)
            ->add('description',TextareaType::class)
            ->add('tags',CollectionType::class,['entry_type'=>HiddenType::class,'allow_add'=>true])
            ->add('sections',CollectionType::class,['entry_type' => IntegerType::class, 'allow_add' => true])
            ->add('pictureFile',FileType::class)
            ->add('documentFile',FileType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return self::name;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UniversityBundle\Entity\Book'
        ));
    }
}