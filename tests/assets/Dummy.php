<?php

namespace PBaszak\DedicatedMapper\Tests\assets;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

class Dummy
{
    public function __construct(
        #[Assert\NotBlank()]
        public string $id,

        #[Assert\Length(min: 3, max: 255)]
        public string $name,
        public string $description,
        public EmbeddedDTO $_embedded,
    ) {
    }
}

class EmbeddedDTO
{
    public function __construct(
        public int $page,
        #[SerializedName('pageSize')]
        public int $pageSize,
        public int $total,
        /** @var ItemDTO[] */
        #[Type("array<PBaszak\DedicatedMapper\Tests\assets\ItemDTO>")]
        public array $items,
    ) {
    }
}

class ItemDTO
{
    public function __construct(
        public string $id,
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
        #[Type('array<string>')]
        #[SerializedName('availableActions')]
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
