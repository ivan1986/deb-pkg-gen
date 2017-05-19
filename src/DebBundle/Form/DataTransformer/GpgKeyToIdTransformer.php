<?php

namespace Ivan1986\DebBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;
use Ivan1986\DebBundle\Repository\GpgKeyRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GpgKeyToIdTransformer implements DataTransformerInterface
{
    /** @var ObjectManager */
    private $om;

    /** @var string */
    private $server;

    /**
     * @param ObjectManager $om
     * @param string        $server
     */
    public function __construct(ObjectManager $om, $server)
    {
        $this->om = $om;
        $this->server = $server;
    }

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * @param GpgKey $value The value in the original representation
     *
     * @throws TransformationFailedException when the transformation fails
     *
     * @return array The value in the transformed representation
     */
    public function transform($value)
    {
        return [
            'id' => $value ? $value->getId() : '',
            'file' => '',
        ];
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * @param string $value The value in the transformed representation
     *
     * @throws TransformationFailedException when the transformation fails
     *
     * @return GpgKey The value in the original representation
     */
    public function reverseTransform($value)
    {
        $r = $this->om->getRepository('Ivan1986DebBundle:GpgKey');
        /* @var $r GpgKeyRepository */
        try {
            if (!empty($value['file'])) {
                $file = $value['file'];
                if ($file instanceof UploadedFile) {
                    return $r->getFromFile($file);
                }
            }
            $id = $value['id'];
            if (strpos($id, '/')) {
                $id = explode('/', $id)[1];
            }

            return $r->getFromServer($id, $this->server);
        } catch (GpgNotFoundException $e) {
            throw new TransformationFailedException();
        }
    }
}
