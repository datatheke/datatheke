<?php

namespace Datatheke\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\ODM\MongoDB\DocumentRepository;

use Datatheke\Bundle\CoreBundle\Manager\ItemManager;

class ItemType extends AbstractType
{
    protected $translator;
    protected $itemManager;

    public function __construct(TranslatorInterface $translator, ItemManager $itemManager)
    {
        $this->translator = $translator;
        $this->itemManager = $itemManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $collection = $options['collection'];

        foreach ($collection->getFields() as $field) {

            if ('collection' === $field->getType()) {
                $builder->add('_'.$field->getId(), 'document', array(
                    'class'            => $this->itemManager->getClass($field->getCollection()),
                    'document_manager' => 'default',
                    'required'         => false,
                    'query_builder'    => function (DocumentRepository $repository) {
                                                return $repository
                                                    ->createQueryBuilder()
                                                    ->field('deleted')->equals(null)
                                                ;
                                            },
                    'label'            => $field->getLabel(),
                    'multiple'         => $field->isMultiple() ? true : false
                ));
            } elseif ($field->isMultiple()) {
                $builder->add('_'.$field->getId(), 'collection', array(
                    'type'         => 'datatheke_field_'.$field->getType(),
                    'required'     => false,
                    'label'        => $field->getLabel(),
                    'by_reference' => false,
                    'allow_add'    => true,
                    'allow_delete' => true
                ));
            } elseif ('date' === $field->getType()) {
                $builder->add('_'.$field->getId(), 'date', array(
                    'required' => false,
                    'label'    => $field->getLabel(),
                    'widget'   => 'single_text',
                    'format'   => 'dd/MM/yyyy',
                    'attr'     => array('class' => 'date')
                ));
            } elseif (in_array($field->getType(), array('string', 'textarea'))) {
                $type = $field->getType();
                if ($type == 'string') {
                    $type = 'text';
                }
                $builder->add('_'.$field->getId(), $type, array(
                    'required' => false,
                    'label'    => $field->getLabel()
                ));
            } else {
                $builder->add('_'.$field->getId(), 'datatheke_field_'.$field->getType(), array(
                    'required'         => false,
                    'label'            => $field->getLabel(),
                ));
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('collection'));
    }

    public function getName()
    {
        return 'datatheke_item';
    }
}
