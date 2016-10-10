<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',HiddenType::class)
            ->add('pictureFile',FileType::class)
            ->add('description',TextareaType::class)
            ->add('date',DateType::class,array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
            ))
            ->add('tags',CollectionType::class,['entry_type'=>HiddenType::class,'allow_add'=>true])
            ->add('duration',IntegerType::class)
            ->add('sections',CollectionType::class,['entry_type' => IntegerType::class, 'allow_add' => true])
        ;
    }

    public function getBlockPrefix()
    {
        return 'uniEvent';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UniversityBundle\Entity\Event'
        ));
    }
}