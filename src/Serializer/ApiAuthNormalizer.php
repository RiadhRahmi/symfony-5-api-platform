<?php


namespace App\Serializer;


use App\Attribute\ApiAuthGroups;

use App\Security\Voter\UserOwnedVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ApiAuthNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{

    use NormalizerAwareTrait;

    private const ALREADY_CALLED_NORMALIZER = 'PostApiNormalizerAlreadyCalled';
    private  $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return false;
        if (!is_object($data)) {
            return false;
        }
        $class = new \ReflectionClass(get_class($data));
        $classAttributes = $class->getAttributes(ApiAuthGroups::class);
        $alreadyCalled = $context[self::ALREADY_CALLED_NORMALIZER] ?? false;
        return $alreadyCalled === false && !empty($classAttributes);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $class = new \ReflectionClass(get_class($object));
        $apiAuthGroups = $class->getAttributes(ApiAuthGroups::class)[0]->newInstance();
        foreach ($apiAuthGroups->groups as $role => $groups) {
            if ($this->authorizationChecker->isGranted($role, $object)) {
                $context['groups'] = array_merge($context['groups'] ?? [], $groups);
            }
        }
        $context[self::ALREADY_CALLED_NORMALIZER] = true;
        return $this->normalizer->normalize($object, $format, $context);
    }
}
