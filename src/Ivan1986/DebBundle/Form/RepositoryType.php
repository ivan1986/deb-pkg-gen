<?php

namespace Ivan1986\DebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ivan1986\DebBundle\Form\Type\GpgKeyType;
use Ivan1986\DebBundle\Form\DataTransformer\RepositoryToStringTransformer;

class RepositoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repoString', null, array(
                'label' => 'Адрес репозитория',
                'attr' => array('class' => 'span5'),
            ))
            ->add('bin', null, array(
                'label' => 'В репозитории есть бинарные пакеты',
            ))
            ->add('src', null, array(
                'label' => 'В репозитории есть пакеты с исходным кодим',
            ))
            ->add('name', null, array(
                'label' => 'Имя пакета для подключения',
            ))
            ->add('key', 'gpgkey_selector', array(
                'label' => 'GPG key ID',
            ))
        ;
    }

    public function getName()
    {
        return 'ivan1986_debbundle_repositorytype';
    }

}
