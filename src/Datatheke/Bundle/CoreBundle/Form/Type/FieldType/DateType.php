<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Datatheke\Bundle\CoreBundle\Document\FieldType\Date;

class DateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', 'date', array(
                'required' => false,
                'label' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\FieldType\Date',
            'empty_data' => function (FormInterface $form) {
                return new Date();
            }
        ));
    }

    public function getName()
    {
        return 'datatheke_field_date';
    }
}
