<?php

namespace Ivan1986\DebBundle\Form\Type;

use Ivan1986\DebBundle\Form\Type\GpgKeyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RepositoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('show_legend', false);
        $builder
            ->add('repoString', null, [
                'label' => 'Адрес репозитория',
                'attr' => ['class' => 'span5'],
            ])
            ->add('bin', null, [
                'label' => 'В репозитории есть бинарные пакеты',
                'required' => false,
            ])
            ->add('src', null, [
                'label' => 'В репозитории есть пакеты с исходным кодом',
                'required' => false,
            ])
            ->add('name', null, [
                'label' => 'Имя пакета для подключения',
            ])
            ->add('key', GpgKeyType::class, [
                'label' => 'GPG ключ',
            ])
        ;
    }

    public function getName()
    {
        return 'std';
    }
}
