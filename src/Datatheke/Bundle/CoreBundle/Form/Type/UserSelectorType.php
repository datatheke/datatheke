<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Datatheke\Bundle\CoreBundle\Form\DataTransformer\UsernameToUserTransformer;

class UserSelectorType extends AbstractType
{
    protected $translator;
    protected $transformer;

    public function __construct(TranslatorInterface $translator, UsernameToUserTransformer $transformer)
    {
        $this->translator = $translator;
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => $this->translator->trans('This user does not exist'),
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\User',
            'empty_data' => null
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'datatheke_user_selector';
    }
}
