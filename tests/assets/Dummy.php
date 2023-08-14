<?php

namespace PBaszak\DedicatedMapper\Tests\assets;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

class Dummy
{
    public function __construct(
        #[Assert\NotBlank()]
        #[SymfonySerializer\Groups('test')]
        public string $id,

        #[Assert\Length(min: 3, max: 255)]
        #[SymfonySerializer\Groups('test')]
        public string $name,
        public string $description,
        #[SymfonySerializer\Groups('test')]
        public EmbeddedDTO $_embedded,
    ) {
    }
}

class EmbeddedDTO
{
    public function __construct(
        #[SymfonySerializer\Groups('test')]
        public int $page,

        #[SymfonySerializer\Groups('test')]
        #[JMS\SerializedName('pageSize')]
        public int $pageSize,
        #[SymfonySerializer\Groups('test')]
        public int $total,
        /** @var ItemDTO[] */
        #[JMS\Type("array<PBaszak\DedicatedMapper\Tests\assets\ItemDTO>")]
        public array $items,
    ) {
    }
}

class ItemDTO
{
    public function __construct(
        #[SymfonySerializer\Groups('test')]
        public string $id,
        #[SymfonySerializer\Groups('test')]
        public string $name,
        public string $description,
        public float $price,
        #[Assert\Choice(choices: ['PLN', 'EUR', 'USD'])]
        public string $currency,
        public int $quantity,
        public string $type,
        public string $category,
        public int $vat,
        public MetadataDTO $metadata,
        public \DateTime $created_at,
        public \DateTime $updated_at,
        /** @var array<string> */
        #[JMS\Type('array<string>')]
        #[JMS\SerializedName('availableActions')]
        public array $availableActions,
    ) {
    }
}

class MetadataDTO
{
    public function __construct(
        public string $test,
        public float $test2,
    ) {
    }
}
