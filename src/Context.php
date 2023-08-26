<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

class Context
{
    /**
     * @param string[] $groups
     * @param ModificatorInterface[] $modificators
     * @param bool $isCollection If `true` then incoming data must be treated 
     *                          as collection of blueprint representations
     * @param bool $skipEmptyProperties If `true` then empty properties will be 
     *                          skipped, otherwise not found callback will be used 
     *                          or exception will be thrown
     */
    public function __construct(
        public readonly array $groups = [],
        public readonly array $modificators = [],
        public readonly bool $isCollection = false,
        public readonly bool $skipEmptyProperties = false,
        public readonly bool $correctTypesBeforeAssignment = false,
    ) {}
}
