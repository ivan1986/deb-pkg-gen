<?php

namespace Ivan1986\DebBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ivan1986\DebBundle\Form\DataTransformer\GpgKeyToIdTransformer;
use Doctrine\Common\Persistence\ObjectManager;

class GpgKeyType extends AbstractType
{
    /** @var ObjectManager */
    private $om;

    /** @param ObjectManager $om */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $transformer = new GpgKeyToIdTransformer($this->om);
        $builder->resetClientTransformers();
        $builder->appendClientTransformer($transformer);
    }

    public function getDefaultOptions()
    {
        return array(
            'invalid_message' => 'This Key not found in server',
        );
    }

    public function getParent(array $options)
    {
        return 'text';
    }

    public function getName()
    {
        return 'gpgkey_selector';
    }
}
