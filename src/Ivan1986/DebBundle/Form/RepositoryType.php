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
        $builder->add('repoString')
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