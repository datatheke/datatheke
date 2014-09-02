<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CommentType extends AbstractType
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'required' => false,
                'label' => $this->translator->trans('Subject')
            ))
            ->add('body', 'textarea', array(
                'required' => true,
                'label' => $this->translator->trans('Comment')
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\Comment'
        ));
    }

    public function getName()
    {
        return 'datatheke_comment';
    }
}
