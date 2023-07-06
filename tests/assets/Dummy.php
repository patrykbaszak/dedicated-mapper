<?php

namespace PBaszak\MessengerMapperBundle\Tests\assets;

use JMS\Serializer\Annotation\Type;

class Dummy
{
    public string $id;
    public string $name;
    public string $description;
    public EmbeddedDTO $_embedded;
}

class EmbeddedDTO
{
    public int $page;
    public int $pageSize;
    public int $total;
    /** @var ItemDTO[] */
    #[Type("array<PBaszak\MessengerMapperBundle\Tests\assets\ItemDTO>")]
    public array $items;
}

class ItemDTO
{
    public string $id;
    public string $name;
    public string $description;
    public float $price;
    public string $currency;
    public int $quantity;
    public string $type;
    public string $category;
    public int $vat;
    public MetadataDTO $metadata;
    public \DateTime $created_at;
    public \DateTime $updated_at;
    /** @var array<string> */
    #[Type('array<string>')]
    public array $availableActions;
}

class MetadataDTO
{
    public string $test;
    public float $test2;
}
