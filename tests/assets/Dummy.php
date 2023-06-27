<?php

namespace PBaszak\MessengerMapperBundle\Tests\Helper;

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
    public array $items; // zawiera obiekty typu ItemDTO
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
    public string $created_at;
    public string $updated_at;
    public array $availableActions;
}

class MetadataDTO
{
    public string $test;
    public float $test2;
}
