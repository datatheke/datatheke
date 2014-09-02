<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Datatheke\Bundle\CoreBundle\Document\Library;
use Datatheke\Bundle\CoreBundle\Document\Field;

class FieldType extends AbstractType
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $that = $this;
        $library = $options['library'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($that, $library) {
            $that->preSetData($event, $library);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($that) {
            $that->preSubmit($event);
        });

        $builder
            ->add('label', 'text', array(
                'required' => true,
                'label'    => $this->translator->trans('Field name')
            ))
        ;
    }

    public function preSetData(FormEvent $event, Library $library)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if ($data instanceof Field && $data->getId()) {
            $this->edit($form);
        } else {
            $this->create($form, $library);
        }
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (isset($data['type'])) {
            // @TODO: if type != 'collection', the field collection should be null
        }
    }

    protected function create(FormInterface $form, Library $library)
    {
        $form
            ->add('type', 'hidden')
            ->add('multiple', 'checkbox', array(
                'required'        => false,
                'label'           => 'Multiple',
            ))
            ->add('collection', 'document', array(
                'class'           => 'DatathekeCoreBundle:Collection',
                'property'        => 'name',
                'required'        => false,
                'label'           => 'Etagère',
                'error_bubbling'  => true,
                'query_builder'   =>
                    function ($repository) use ($library) {
                        return $repository
                            ->createQueryBuilder('collection')
                            ->field('library.$id')->equals(new \MongoId($library->getId()))
                            ->field('deleted')->equals(false)
                        ;
                    },
            ))
        ;
    }

    protected function edit(FormInterface $form)
    {
        $form->add('deleted', 'hidden');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('library'));

        $resolver->setDefaults(array(
            'data_class' => 'Datatheke\Bundle\CoreBundle\Document\Field'
        ));
    }

    public function getName()
    {
        return 'datatheke_field';
    }
}
