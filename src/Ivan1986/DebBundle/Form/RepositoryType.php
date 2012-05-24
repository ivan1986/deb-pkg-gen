<?php

namespace Ivan1986\DebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ivan1986\DebBundle\Form\Type\GpgKeyType;

class RepositoryType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('release')
            ->add('components', 'collection')
            ->add('bin')
            ->add('src')
            ->add('name')
            ->add('key', 'gpgkey_selector')
        ;
    }

    public function getName()
    {
        return 'ivan1986_debbundle_repositorytype';
    }
}
