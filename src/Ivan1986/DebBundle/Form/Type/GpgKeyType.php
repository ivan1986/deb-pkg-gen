<?php

namespace Ivan1986\DebBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Ivan1986\DebBundle\Form\DataTransformer\GpgKeyToIdTransformer;
use Doctrine\Common\Persistence\ObjectManager;

class GpgKeyType extends AbstractType
{
    /** @var ObjectManager */
    private $om;

    /** @var Translator */
    private $translator;

    /** @var string */
    private $server;

    /** @param ObjectManager $om */
    public function __construct(ObjectManager $om, Translator $translator, $server)
    {
        $this->om = $om;
        $this->translator = $translator;
        $this->server = $server;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'invalid_message' => $this->translator->trans('Ошибка загрузки ключа'),
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new GpgKeyToIdTransformer($this->om, $this->server);
        $builder->add('id', null, array(
            'label' => 'ID ключа на сервере',
            'required' => false,
        ));
        $builder->add('file', 'file', array(
            'label' => 'или локальный файл',
            'required' => false,
        ));
        $builder->addViewTransformer($transformer);
    }

    public function getName()
    {
        return 'gpgkey_selector';
    }
}
