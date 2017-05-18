<?php

namespace Ivan1986\DebBundle\Form;

use JMS\DiExtraBundle\Annotation\FormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @FormType
 */
class PpaRepositoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('show_legend', false);
        $builder
            ->add('repoString', null, [
                'label' => 'Адрес PPA репозитория',
                'attr' => ['class' => 'span5'],
            ])
            ->add('name', null, [
                'label' => 'Имя пакета',
                'required' => false,
            ])
        ;
    }

    public function getName()
    {
        return 'ppa';
    }
}
