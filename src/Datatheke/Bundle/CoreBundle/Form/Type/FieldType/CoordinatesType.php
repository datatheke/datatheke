<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Datatheke\Bundle\CoreBundle\Document\FieldType\Coordinates;

class CoordinatesType extends AbstractType
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('longitude', 'number', array(
                'required'  => false,
                'precision' => 6,
                'label'     => $this->translator->trans('Longitude'),
            ))
            ->add('latitude', 'number', array(
                'required'  => false,
                'precision' => 6,
                'label'     => $this->translator->trans('Latitude'),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\FieldType\Coordinates',
            'empty_data' => function (FormInterface $form) {
                return new Coordinates();
            }
        ));
    }

    public function getName()
    {
        return 'datatheke_field_coordinates';
    }
}
