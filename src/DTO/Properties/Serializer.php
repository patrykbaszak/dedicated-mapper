<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO\Properties;

use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\SerializedPath;

class Serializer
{
    /**
     * @param Context[] $contexts
     */
    public function __construct(
        public array $contexts = [], // not used
        public ?Groups $groups = null, // used
        public ?Ignore $ignore = null, // used
        public ?MaxDepth $maxDepth = null, // not used
        public ?SerializedName $serializedName = null, // used
        public ?SerializedPath $serializedPath = null, // used
    ) {
    }
}
