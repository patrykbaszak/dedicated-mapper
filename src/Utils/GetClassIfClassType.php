<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Utils;

trait GetClassIfClassType
{
    /** @return class-string|null */
    private function getClassIfClassType(?\ReflectionType $type): ?string
    {
        if (null === $type) {
            return null;
        }

        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            if (class_exists($typeName)) {
                /* @phpstan-ignore-next-line */
                return '\\'.ltrim($typeName, '\\');
            }
        }

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            $typeList = $type->getTypes();
            foreach ($typeList as $innerType) {
                $class = $this->getClassIfClassType($innerType);
                if (null !== $class) {
                    return $class;
                }
            }
        }

        return null;
    }
}
