<?php

namespace Ivan1986\DebBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Ivan1986\DebBundle\Form\DataTransformer\GpgKeyToIdTransformer;
use Doctrine\Common\Persistence\ObjectManager;

class GpgKeyType extends AbstractType
{
    /** @var ObjectManager */
    private $om;

    /** @var string */
    private $server;

    /** @param ObjectManager $om */
    public function __construct(ObjectManager $om, $server)
    {
        $this->om = $om;
        $this->server = $server;
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

    public function getDefaultOptions()
    {
        return array(
            'invalid_message' => 'This Key not found in server',
        );
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'gpgkey_selector';
    }
}
