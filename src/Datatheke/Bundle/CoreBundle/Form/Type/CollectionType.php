<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CollectionType extends AbstractType
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'required' => true,
                'label' => $this->translator->trans('Nom')
            ))
            ->add('description', 'textarea', array(
                'required' => false,
                'label' => $this->translator->trans('Description')
            ))
            ->add('fields', 'collection', array(
                    'type'         => 'datatheke_field',
                    'options'      => array('library' => $options['library']),
                    'by_reference' => false,
                    'allow_add'    => true,
                    )
            )
        ;

        if (false === $options['api']) {
            $builder->add('imageUpload', 'file', array(
                'required' => false,
                'label' => $this->translator->trans('Image')
            ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('library'));

        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\Collection',
            'api' => false
        ));
    }

    public function getName()
    {
        return 'datatheke_collection';
    }
}
