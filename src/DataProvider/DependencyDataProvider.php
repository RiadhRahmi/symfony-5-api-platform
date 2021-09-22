<?php

namespace App\DataProvider;

use Ramsey\Uuid\Uuid;
use App\Entity\Dependency;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;

class DependencyDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface, ItemDataProviderInterface
{

    private $rootPath;


    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    private function getDependencies(): array
    {
        $path = $this->rootPath . '/composer.json';
        $json = json_decode(file_get_contents($path), true);
        return $json['require'];
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {

        $data = [
            ["a" => "hello", "b" => "bonjour"],
            ["aa" => "hello", "bb" => "bonjour"],
            ["aaa" => "hello", "bbb" => "bonjour"],
        ];
        $items = [];
        foreach ($this->getDependencies() as $name => $version) {
            $items[] = new Dependency(Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString(), $name, $version);
        }
        return $items;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === Dependency::class;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $data = ["a" => "hello", "b" => "bonjour"];
        $dependencies = $this->getDependencies();
        foreach ($dependencies as $name => $version) {
            $uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();
            if ($uuid === $id) {
                return new Dependency($uuid, $name, $version);
            }
        }
        return null;
    }
}
