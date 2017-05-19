<?php

namespace Ivan1986\DebBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class GpgKeyRepository extends EntityRepository
{
    /**
     * @var \gnupg
     */
    protected $gnupg;

    /**
     * GpgKeyRepository constructor.
     */
    public function __construct($em, ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->gnupg = new \gnupg();
    }

    /**
     * Get key from server and store to database.
     *
     * @param string $keyId      key id
     * @param string $serverName key server
     *
     * @throws GpgNotFoundException
     *
     * @return GpgKey
     */
    public function getFromServer($keyId, $serverName)
    {
        $key = $this->findOneBy(['id' => $keyId]);

        if ($key) {
            return $key;
        }

        $client = new \GuzzleHttp\Client(['http_errors' => false]);
        $data = $client->get('http://'.$serverName.':11371/pks/lookup?op=get&search=0x'.$keyId)->getBody();
        $start = strpos($data, '-----BEGIN PGP PUBLIC KEY BLOCK-----');
        if ($start === false) {
            throw new GpgNotFoundException($keyId);
        }
        $PublicKey = $this->gnupg->import($data);
        $this->gnupg->setarmor(false);

        return $this->saveKey($keyId, $PublicKey);
    }

    /**
     * Get key from file and store to database.
     *
     * @param File $file
     *
     * @throws GpgNotFoundException
     *
     * @return GpgKey
     */
    public function getFromFile(File $file)
    {
        if (!$file->isReadable()) {
            throw new GpgNotFoundException(0);
        }
        $data = file_get_contents($file->getRealPath());
        $PublicKey = $this->gnupg->import($data);
        if (empty($PublicKey['fingerprint'])) {
            throw new GpgNotFoundException(0);
        }
        $this->gnupg->setarmor(false);
        $info = $this->gnupg->keyinfo($PublicKey['fingerprint']);
        if (empty($info[0]['subkeys'][0])) {
            throw new GpgNotFoundException(0);
        }
        $keyId = $info[0]['subkeys'][0]['keyid'];
        $keyId = substr($keyId, 8);
        if (strlen($keyId) != 8) {
            throw new GpgNotFoundException(0);
        }

        return $this->findOneBy(['id' => $keyId]) ?: $this->saveKey($keyId, $PublicKey);
    }

    /**
     * Save key to database.
     *
     * @param $keyId
     * @param $PublicKey
     *
     * @return GpgKey
     */
    private function saveKey($keyId, $PublicKey): GpgKey
    {
        $key = new GpgKey();

        $key->setId($keyId);
        $key->setData($this->gnupg->export($PublicKey['fingerprint']));
        $key->setFingerprint($PublicKey['fingerprint']);
        $this->_em->persist($key);
        $this->_em->flush($key);

        return $key;
    }
}
