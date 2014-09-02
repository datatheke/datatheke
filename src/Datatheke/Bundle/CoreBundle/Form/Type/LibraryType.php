<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LibraryType extends AbstractType
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
                'label' => $this->translator->trans('Name')
            ))
            ->add('description', 'textarea', array(
                'required' => false,
                'label' => $this->translator->trans('Description')
            ))
            ->add('public', 'checkbox', array(
                'required' => false,
                'label' => $this->translator->trans('Public')
            ))
            ->add('collaborative', 'checkbox', array(
                'required' => false,
                'label' => $this->translator->trans('Collaborative')
            ))
        ;

        if (false === $options['api']) {
            $builder
                ->add('imageUpload', 'file', array(
                    'required' => false,
                    'label' => $this->translator->trans('Image')
                ))
                ->add('shares', 'collection', array(
                    'type'         => 'datatheke_share',
                    'by_reference' => false,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'label'        => $this->translator->trans('Users')
                ))
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\Library',
            'api' => false
        ));
    }

    public function getName()
    {
        return 'datatheke_library';
    }
}
