<?php

namespace Ivan1986\DebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\FormType;

/**
 * @FormType
 */
class PpaRepositoryType extends AbstractType
{
    /**
     * @Inject
     */
    private $translator;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('show_legend', false);
        $builder
            ->add('repoString', null, array(
                'label' => 'Адрес PPA репозитория',
                'attr' => array('class' => 'span5'),
            ))
            ->add('name', null, array(
                'label' => 'Имя пакета',
                'required' => false,
            ))
        ;
    }

    public function getName()
    {
        return 'ppa';
    }

}
