<?php

declare(strict_types=1);

namespace TwentytwoLabs\FeatureFlagBundle\Bridge\ApiService\Storage;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use TwentytwoLabs\ApiServiceBundle\ApiService;
use TwentytwoLabs\ApiServiceBundle\Model\Collection;
use TwentytwoLabs\ApiServiceBundle\Model\ErrorInterface;
use TwentytwoLabs\ApiServiceBundle\Model\ResourceInterface;
use TwentytwoLabs\FeatureFlagBundle\Model\FeatureInterface;
use TwentytwoLabs\FeatureFlagBundle\Storage\StorageInterface;

final class ApiServiceStorage implements StorageInterface
{
    private DenormalizerInterface $denormalizer;
    private ApiService $client;
    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(DenormalizerInterface $denormalizer, ApiService $client, array $options)
    {
        $this->denormalizer = $denormalizer;
        $this->client = $client;
        $this->options = $options;
    }

    public function all(): array
    {
        $page = 1;
        $features = [];
        $mapper = $this->options['collection']['mapper'];

        do {
            /** @var ErrorInterface|ResourceInterface $response */
            $response = $this->client->call(
                operationId: $this->options['collection']['operationId'],
                params: array_merge($this->options['collection']['params'], [$mapper['page'] => $page])
            );

            ++$page;

            if (!$response instanceof Collection) {
                return [];
            }

            foreach ($response->getData() as $item) {
                $features[] = $this->denormalizer->denormalize($item, FeatureInterface::class);
            }
        } while ($page <= ($response->getPagination()?->getTotalPages() ?? 1));

        return $features;
    }

    public function get(string $key): ?FeatureInterface
    {
        $mapper = $this->options['item']['mapper'];

        $response = $this->client->call(
            operationId: $this->options['item']['operationId'],
            params: [$mapper['identifier'] => $key]
        );

        if ($response instanceof ResourceInterface) {
            return $this->denormalizer->denormalize($response->getData(), FeatureInterface::class);
        }

        return null;
    }
}
