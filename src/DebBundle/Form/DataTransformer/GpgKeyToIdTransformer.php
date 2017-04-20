<?php

namespace Ivan1986\DebBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;
use Ivan1986\DebBundle\Model\GpgLoader;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GpgKeyToIdTransformer implements DataTransformerInterface
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

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * This method is called on two occasions inside a form field:
     *
     * 1. When the form field is initialized with the data attached from the datasource (object or array).
     * 2. When data from a request is bound using {@link Form::bind()} to transform the new input data
     *    back into the renderable format. For example if you have a date field and bind '2009-10-10' onto
     *    it you might accept this value because its easily parsed, but the transformer still writes back
     *    "2009/10/10" onto the form field (for further displaying or other purposes).
     *
     * This method must be able to deal with empty values. Usually this will
     * be NULL, but depending on your implementation other empty values are
     * possible as well (such as empty strings). The reasoning behind this is
     * that value transformers must be chainable. If the transform() method
     * of the first value transformer outputs NULL, the second value transformer
     * must be able to process that value.
     *
     * By convention, transform() should return an empty string if NULL is
     * passed.
     *
     * @param GpgKey $value The value in the original representation
     *
     * @throws TransformationFailedException when the transformation fails
     *
     * @return string The value in the transformed representation
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
     * This method is called when {@link Form::bind()} is called to transform the requests tainted data
     * into an acceptable format for your data processing/model layer.
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as empty strings). The reasoning behind
     * this is that value transformers must be chainable. If the
     * reverseTransform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param string $value The value in the transformed representation
     *
     * @throws TransformationFailedException when the transformation fails
     *
     * @return GpgKey The value in the original representation
     */
    public function reverseTransform($value)
    {
        if (!empty($value['file'])) {
            $file = $value['file'];
            if ($file instanceof UploadedFile) {
                /* @var $file UploadedFile */
                try {
                    $key = GpgLoader::getFromFile($file->getRealPath());
                } catch (GpgNotFoundException $e) {
                    throw new TransformationFailedException();
                }
                $r = $this->om->getRepository('Ivan1986DebBundle:GpgKey');
                /** @var $r GpgKeyRepository */
                $exist = $r->findOneBy(['id' => $key->getId()]);
                if ($exist) {
                    return $exist;
                }
                $this->om->persist($key);

                return $key;
            }
        }
        $id = $value['id'];
        if (strpos($id, '/')) {
            $id = explode('/', $id);
            $id = $id[1];
        }
        $r = $this->om->getRepository('Ivan1986DebBundle:GpgKey');
        /** @var $r GpgKeyRepository */
        $key = $r->findOneBy(['id' => $id]);
        if ($key) {
            return $key;
        }
        try {
            $key = GpgLoader::getFromServer($id, $this->server);
        } catch (GpgNotFoundException $e) {
            throw new TransformationFailedException();
        }
        $this->om->persist($key);

        return $key;
    }
}
