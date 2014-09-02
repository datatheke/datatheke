<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ShareType extends AbstractType
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'datatheke_user_selector')
            ->add('write', 'checkbox', array(
                'required' => false,
                'label' => $this->translator->trans('Write')
            ))
            ->add('admin', 'checkbox', array(
                'required' => false,
                'label' => $this->translator->trans('Administrate (Create/edit collections)')
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\Share'
        ));
    }

    public function getName()
    {
        return 'datatheke_share';
    }
}
