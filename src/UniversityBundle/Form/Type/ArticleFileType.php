<?php

namespace UniversityBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFileType extends BaseAbstractType
{
    const name = 'uni_article';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile',FileType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return self::name;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UniversityBundle\Entity\Article'
        ));
    }
}