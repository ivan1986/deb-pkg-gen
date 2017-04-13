<?php

namespace Ivan1986\DebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LinkPackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('show_legend', false);
        $builder
            ->add('link', null, [
                'label' => 'Адрес файла пакета',
                'attr' => ['class' => 'span8'],
            ])
            ->add('info', 'textarea', [
                'label' => 'То, что пишется в Packages',
                'attr' => ['class' => 'span9', 'rows' => 15],
            ])
        ;
    }

    public function getName()
    {
        return 'std';
    }
}
