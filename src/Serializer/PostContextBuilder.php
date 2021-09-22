<?php


namespace App\Serializer;


use ApiPlatform\Core\Exception\RuntimeException;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PostContextBuilder implements SerializerContextBuilderInterface
{

    private  $decorated;
    private  $authorizationChecker;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;
        if (
            $resourceClass === Article::class &&
            isset($context['groups']) &&
            $this->authorizationChecker->isGranted('ROLE_USER')
        ) {
            $context['groups'][] = 'read:collection:User';
            $context['groups'][] = 'article:read';
        }
        return $context;
    }
}
