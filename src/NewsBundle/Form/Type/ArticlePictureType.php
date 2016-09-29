<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 29.09.16
 * Time: 09:17
 */

namespace NewsBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticlePictureType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile','file')
        ;
    }

    public function getName()
    {
        return 'article';
    }

}