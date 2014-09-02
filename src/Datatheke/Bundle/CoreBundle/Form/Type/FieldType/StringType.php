<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Datatheke\Bundle\CoreBundle\Document\FieldType\String;

class StringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', 'text', array(
                'required' => false,
                'label' => false
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\FieldType\String',
            'empty_data' => function (FormInterface $form) {
                return new String();
            }
        ));
    }

    public function getName()
    {
        return 'datatheke_field_string';
    }
}
