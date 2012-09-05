<?php

namespace Ivan1986\DebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;

class LinkPackageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('show_legend', false);
        $builder
            ->add('link', null, array(
                'label' => 'Адрес файла пакета',
                'attr' => array('class' => 'span5'),
            ))
            ->add('file', null, array(
                'label' => 'Имя файла в репозитории',
            ))
            ->add('info', 'textarea', array(
                'label' => 'То, что пишется в Packages',
            ))
        ;
    }

    public function getName()
    {
        return 'std';
    }

}
