<?php

namespace Ivan1986\DebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;

class RepositoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('show_legend', false);
        $builder
            ->add('repoString', null, array(
                'label' => 'Адрес репозитория',
                'attr' => array('class' => 'span5'),
            ))
            ->add('bin', null, array(
                'label' => 'В репозитории есть бинарные пакеты',
                'required' => false,
            ))
            ->add('src', null, array(
                'label' => 'В репозитории есть пакеты с исходным кодом',
                'required' => false,
            ))
            ->add('name', null, array(
                'label' => 'Имя пакета для подключения',
            ))
            ->add('key', 'gpgkey_selector', array(
                'label' => 'GPG ключ',
            ))
        ;
    }

    public function getName()
    {
        return 'ivan1986_debbundle_repositorytype';
    }

}
