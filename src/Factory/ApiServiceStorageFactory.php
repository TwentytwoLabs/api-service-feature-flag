<?php

declare(strict_types=1);

namespace TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Factory;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use TwentytwoLabs\ApiServiceBundle\ApiService;
use TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Storage\ApiServiceStorage;
use TwentytwoLabs\FeatureFlagBundle\Factory\AbstractStorageFactory;
use TwentytwoLabs\FeatureFlagBundle\Storage\StorageInterface;

final class ApiServiceStorageFactory extends AbstractStorageFactory
{
    private DenormalizerInterface $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    protected function configureOptionResolver(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['client', 'collection', 'item'])
            ->setAllowedTypes('client', ApiService::class)
            ->setAllowedTypes('collection', 'array')
            ->setDefault('collection', function (OptionsResolver $collectionResolver): void {
                $collectionResolver->setRequired(['operationId']);
                $collectionResolver->setDefault('mapper', function (OptionsResolver $mapperResolver): void {
                    $mapperResolver->setDefault('page', 'page');
                    $mapperResolver->setAllowedTypes('page', 'string');
                });
                $collectionResolver->setDefault('params', []);
                $collectionResolver->setAllowedTypes('operationId', 'string');
                $collectionResolver->setAllowedTypes('params', 'array');
            })
            ->setDefault('item', function (OptionsResolver $itemResolver): void {
                $itemResolver->setRequired(['operationId']);
                $itemResolver->setDefault('mapper', function (OptionsResolver $mapperResolver): void {
                    $mapperResolver->setRequired(['identifier']);
                    $mapperResolver->setAllowedTypes('identifier', ['string']);
                });
                $itemResolver->setDefault('params', []);
                $itemResolver->setAllowedTypes('operationId', 'string');
                $itemResolver->setAllowedTypes('params', 'array');
            })
        ;
    }

    public function createStorage(string $storageName, array $options = []): StorageInterface
    {
        $options = $this->validate($storageName, $options);
        $client = $options['client'];
        unset($options['client']);

        return new ApiServiceStorage($this->denormalizer, $client, $options);
    }
}
