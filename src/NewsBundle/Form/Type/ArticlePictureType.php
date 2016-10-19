<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 29.09.16
 * Time: 09:17
 */

namespace NewsBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticlePictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile',FileType::class)
            ->add('files',CollectionType::class,['entry_type'=>FileType::class,'allow_add'=>true])
        ;
    }

    public function getBlockPrefix()
    {
        return ArticleType::NAME;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NewsBundle\Entity\Article'
        ));
    }
}