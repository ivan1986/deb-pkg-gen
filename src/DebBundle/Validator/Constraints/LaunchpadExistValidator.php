<?php

namespace Ivan1986\DebBundle\Validator\Constraints;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;
use Ivan1986\DebBundle\Model\GpgLoader;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LaunchpadExistValidator extends ConstraintValidator
{
    protected $message;
    /**
     * @var Registry
     */
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function validate($value, Constraint $constraint)
    {
        $this->message = $constraint->message;

        /** @var $value \Ivan1986\DebBundle\Entity\PpaRepository */
        if (empty($value->getPpaUrl())) {
            return $this->addMessage();
        }
        $client = new \GuzzleHttp\Client(['http_errors' => false]);
        if ($client->get($value->getPpaUrl())->getStatusCode() != 200) {
            return $this->addMessage();
        }
        //репозиторий существует, заодно получим ключ

        $this->getKeyFromLaunchpad($value);
    }

    private function getKeyFromLaunchpad($value)
    {
        /** @var $value \Ivan1986\DebBundle\Entity\PpaRepository */
        $client = new \GuzzleHttp\Client(['defaults' => [
            'verify' => 'false',
        ]]);
        $data = $client->get($value->getPpaPage())->getBody();
        if (!$data) {
            return $this->addMessage();
        }
        $matches = [];
        preg_match('#<code>\d+R/(.*)</code>#is', $data, $matches);
        if (empty($matches[1])) {
            return $this->addMessage();
        }
        $keyId = $matches[1];

        $r = $this->doctrine->getRepository('Ivan1986DebBundle:GpgKey');
        try {
            $key = $r->getFromServer($keyId, 'keyserver.ubuntu.com');
        } catch (GpgNotFoundException $e) {
            return $this->addMessage();
        }
        $this->doctrine->getManager()->persist($key);
        $value->setKey($key);

        return true;
    }

    private function addMessage()
    {
        $this->context->addViolation($this->message);
    }
}
