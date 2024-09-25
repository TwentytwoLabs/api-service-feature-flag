<?php

declare(strict_types=1);

namespace TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Tests\Storage;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use TwentytwoLabs\ApiServiceBundle\ApiService;
use TwentytwoLabs\ApiServiceBundle\Model\Collection;
use TwentytwoLabs\ApiServiceBundle\Model\ErrorInterface;
use TwentytwoLabs\ApiServiceBundle\Model\Pagination;
use TwentytwoLabs\ApiServiceBundle\Model\ResourceInterface;
use TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Storage\ApiServiceStorage;
use PHPUnit\Framework\TestCase;
use TwentytwoLabs\FeatureFlagBundle\Model\FeatureInterface;

final class ApiServiceStorageTest extends TestCase
{
    private DenormalizerInterface|MockObject $denormalizer;
    private ApiService|MockObject $client;

    protected function setUp(): void
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->client = $this->createMock(ApiService::class);
    }

    public function testShouldNotGetAllFeaturesBecauseThereAreSomeErrors(): void
    {
        $response = $this->createMock(ErrorInterface::class);

        $this->denormalizer->expects($this->never())->method('denormalize');

        $this->client
            ->expects($this->once())
            ->method('call')
            ->with('getFeatureCollection', '', '', ['itemsPerPage' => 1, 'page' => 1])
            ->willReturn($response)
        ;

        $storage = $this->getStorage();
        $this->assertSame([], $storage->all());
    }

    public function testShouldGetAllFeaturesWithoutPagination(): void
    {
        $feature = $this->createMock(FeatureInterface::class);

        $response = new Collection([['title' => 'Lorem Ipsum', 'enabled' => true]], [], null);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with(['title' => 'Lorem Ipsum', 'enabled' => true], FeatureInterface::class)
            ->willReturn($feature)
        ;

        $this->client
            ->expects($this->once())
            ->method('call')
            ->with('getFeatureCollection', '', '', ['itemsPerPage' => 1, 'page' => 1])
            ->willReturn($response)
        ;

        $storage = $this->getStorage();
        $this->assertSame([$feature], $storage->all());
    }

    public function testShouldGetAllFeaturesWithPagination(): void
    {
        $pagination = new Pagination(1, 1, 1, 2);

        $feature = $this->createMock(FeatureInterface::class);

        $responsePage1 = new Collection([['title' => 'Lorem Ipsum', 'enabled' => true]], [], $pagination);

        $responsePage2 = new Collection([['title' => 'Lorem Ipsum', 'enabled' => true]], [], $pagination);

        $this->denormalizer
            ->expects($this->exactly(2))
            ->method('denormalize')
            ->with(['title' => 'Lorem Ipsum', 'enabled' => true], FeatureInterface::class)
            ->willReturn($feature)
        ;

        $callMocker = $this->exactly(2);
        $this->client
            ->expects($callMocker)
            ->method('call')
            ->willReturnCallback(
                function (
                    string $operationId,
                    string $method,
                    string $path,
                    array $params
                ) use (
                    $callMocker,
                    $responsePage1,
                    $responsePage2
                ) {
                    $this->assertSame('getFeatureCollection', $operationId);
                    $this->assertSame('', $method);
                    $this->assertSame('', $path);
                    $this->assertSame(['itemsPerPage' => 1, 'page' => $callMocker->numberOfInvocations()], $params);

                    return match ($callMocker->numberOfInvocations()) {
                        1 => $responsePage1,
                        2 => $responsePage2,
                        default => throw new \Exception(sprintf('The methode %s is call more than %d time', 'call', 2)),
                    };
                }
            )
        ;

        $storage = $this->getStorage();
        $this->assertSame([$feature, $feature], $storage->all());
    }

    public function testShouldNotGetOneFeatureBecauseThereAreSomeErrors(): void
    {
        $response = $this->createMock(ErrorInterface::class);

        $this->denormalizer->expects($this->never())->method('denormalize');

        $this->client
            ->expects($this->once())
            ->method('call')
            ->with('getFeatureItem', '', '', ['uuid' => 'foo'])
            ->willReturn($response)
        ;

        $storage = $this->getStorage();
        $this->assertNull($storage->get('foo'));
    }

    public function testShouldGetOneFeature(): void
    {
        $feature = $this->createMock(FeatureInterface::class);

        $response = $this->createMock(ResourceInterface::class);
        $response
            ->expects($this->once())
            ->method('getData')
            ->willReturn(['title' => 'Lorem Ipsum', 'enabled' => true])
        ;

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with(['title' => 'Lorem Ipsum', 'enabled' => true], FeatureInterface::class)
            ->willReturn($feature)
        ;

        $this->client
            ->expects($this->once())
            ->method('call')
            ->with('getFeatureItem', '', '', ['uuid' => 'foo'])
            ->willReturn($response)
        ;

        $storage = $this->getStorage();
        $this->assertSame($feature, $storage->get('foo'));
    }

    private function getStorage(): ApiServiceStorage
    {
        return new ApiServiceStorage(
            $this->denormalizer,
            $this->client,
            [
                'collection' => [
                    'mapper' => ['page' => 'page'],
                    'operationId' => 'getFeatureCollection',
                    'params' => ['itemsPerPage' => 1],
                ],
                'item' => [
                    'mapper' => ['identifier' => 'uuid'],
                    'operationId' => 'getFeatureItem',
                    'params' => ['x-uuid' => 'foo'],
                ],
            ]
        );
    }
}
