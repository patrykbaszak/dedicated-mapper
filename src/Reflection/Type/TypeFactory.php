<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use PBaszak\DedicatedMapper\Reflection\ReflectionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionType;

class TypeFactory
{
    protected ReflectionFactory $reflectionFactory;

    public function __construct(ReflectionFactory $reflectionFactory = null)
    {
        $this->reflectionFactory = $reflectionFactory ?? new ReflectionFactory();
    }

    public function createClassType(\ReflectionClass $reflection, null|PropertyReflection|TypeInterface $parent): ClassType
    {
        $ref = new \ReflectionClass(ClassType::class);
        /** @var ClassType $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('reflection')->setValue($instance, $this->reflectionFactory->createClassReflection($reflection->getName(), $instance));
        $ref->getProperty('parent')->setValue($instance, $parent);

        return $instance;
    }

    // public function createCollectionType(null|PropertyReflection|TypeInterface $parent): CollectionType
    // {

    // }
    
    protected function createType(PropertyReflection $propertyInstanceWithoutType, int $depth = 0): Type
    {
        if (null === ($ref = $propertyInstanceWithoutType->getReflection())) {
            throw new \Exception('Reflection is null.');
        }

        $type = $ref->getType();
        $phpDocumentatorType = null;
        if (false !== ($docComment = $ref->getDocComment())) {
            /** @var Var_[] $varTags */
            $varTags = DocBlockFactory::createInstance()->create(
                $docComment,
                (new ContextFactory)->createFromReflector($ref)
            )->getTagsByName('var');

            $phpDocumentatorType = $varTags[0]->getType() ?? null;
        }
    }

    /**
     * @return object{"types": string[], "innerTypes": string[]}
     */
    protected function extractTypes(ReflectionType $ref, Type $phpDocumentatorType): object
    {
        
    }
}
