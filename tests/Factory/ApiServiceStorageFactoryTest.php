<?php

declare(strict_types=1);

namespace TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Tests\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use TwentytwoLabs\ApiServiceBundle\ApiService;
use TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Factory\ApiServiceStorageFactory;
use PHPUnit\Framework\TestCase;
use TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Storage\ApiServiceStorage;
use TwentytwoLabs\FeatureFlagBundle\Exception\ConfigurationException;

final class ApiServiceStorageFactoryTest extends TestCase
{
    private DenormalizerInterface|MockObject $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testShouldNotCreateStorageBecauseClientItIsNotAnApiService(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The option "client" with value "api_service.api.default" is expected to be of type "TwentytwoLabs\ApiServiceBundle\ApiService", but is of type "string".');

        $options = [
            'client' => 'api_service.api.default',
            'collection' => ['operationId' => 'getFeatureCollection'],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 'slug']],
        ];

        $factory = $this->getFactory();
        $factory->createStorage('default', $options);
    }

    public function testShouldNotCreateStorageBecauseOperationIdOfCollectionMustBeString(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The option "collection[operationId]" with value 813 is expected to be of type "string", but is of type "int".');

        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 813],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 'slug']],
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    public function testShouldNotCreateStorageBecauseMapperMustBeString(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The option "collection[mapper][page]" with value 8 is expected to be of type "string", but is of type "int".');

        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection', 'mapper' => ['page' => 8]],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 'slug']],
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    public function testShouldNotCreateStorageBecauseOperationIdOfItemMustBeString(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The option "item[operationId]" with value 813 is expected to be of type "string", but is of type "int".');

        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection'],
            'item' => ['operationId' => 813, 'mapper' => ['identifier' => 'slug']],
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    public function testShouldNotCreateStorageBecauseMissingOperationIds(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The required option "collection[operationId]" is missing.');

        $options = [
            'client' => $this->createMock(ApiService::class),
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    public function testShouldNotCreateStorageBecauseMissingIdentifier(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The required option "item[mapper][identifier]" is missing.');

        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection'],
            'item' => ['operationId' => 'getFeatureItem'],
        ];

        $factory = $this->getFactory();
        $factory->createStorage('default', $options);
    }

    public function testShouldNotCreateStorageBecauseIdentifierBecauseIdentifierMustBeString(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The option "item[mapper][identifier]" with value 8 is expected to be of type "string", but is of type "int".');

        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection'],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 8]],
        ];

        $factory = $this->getFactory();
        $factory->createStorage('default', $options);
    }

    public function testShouldNotCreateStorageBecauseParamsIsNotAnArrayInCollectionOperationId(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The option "collection[params]" with value "bar" is expected to be of type "array", but is of type "string".');

        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection', 'params' => 'bar'],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 'slug']],
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    public function testShouldNotCreateStorageBecauseParamsIsNotAnArrayInItemOperationId(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Error while configure storage default. Verify your configuration at "twenty-two-labs.feature-flags.storages.default.options". The option "item[params]" with value "bar" is expected to be of type "array", but is of type "string".');

        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection'],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 'slug'], 'params' => 'bar'],
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    public function testShouldCreateStorageWithDifferentMapper(): void
    {
        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection', 'mapper' => ['page' => 'p']],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 'slug']],
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    public function testShouldCreateStorage(): void
    {
        $options = [
            'client' => $this->createMock(ApiService::class),
            'collection' => ['operationId' => 'getFeatureCollection'],
            'item' => ['operationId' => 'getFeatureItem', 'mapper' => ['identifier' => 'slug']],
        ];

        $factory = $this->getFactory();
        $storage = $factory->createStorage('default', $options);
        $this->assertInstanceOf(ApiServiceStorage::class, $storage);
    }

    private function getFactory(): ApiServiceStorageFactory
    {
        return new ApiServiceStorageFactory($this->denormalizer);
    }
}
