<?php

namespace Ivan1986\DebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ivan1986\DebBundle\Form\Type\GpgKeyType;
use Ivan1986\DebBundle\Form\DataTransformer\RepositoryToStringTransformer;

class PpaRepositoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repoString', null, array(
                'label' => 'Адрес PPA репозитория',
                'attr' => array('class' => 'span5'),
            ))
            ->add('name', null, array(
                'label' => 'Има пакета',
                'required' => false,
            ))
        ;
    }

    public function getName()
    {
        return 'ivan1986_debbundle_pparepositorytype';
    }

}
