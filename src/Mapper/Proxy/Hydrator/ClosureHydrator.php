<?php

declare(strict_types=1);

namespace Cycle\ORM\Mapper\Proxy\Hydrator;

use Closure;
use Cycle\ORM\EntityProxyInterface;
use Cycle\ORM\Reference\ReferenceInterface;
use Cycle\ORM\RelationMap;

/**
 * @internal
 */
class ClosureHydrator
{
    /**
     * @param array<string, PropertyMap> $propertyMaps Array of class properties
     */
    public function hydrate(RelationMap $relMap, array $propertyMaps, object $object, array $data): object
    {
        $isProxy = $object instanceof EntityProxyInterface;

        $properties = $propertyMaps[ClassPropertiesExtractor::KEY_FIELDS]->getProperties();
        $this->setEntityProperties($properties, $object, $data);

        if (!$isProxy) {
            $properties = $propertyMaps[ClassPropertiesExtractor::KEY_RELATIONS]->getProperties();
            if ($properties !== []) {
                $this->setRelationProperties($properties, $relMap, $object, $data);
            }
        }

        foreach ($data as $property => $value) {
            $object->{$property} = $value;
        }

        return $object;
    }

    /**
     * Map private entity properties
     */
    private function setEntityProperties(array $properties, object $object, array &$data): void
    {
        foreach ($properties as $class => $props) {
            if ($class === '') {
                continue;
            }

            Closure::bind(static function (object $object, array $props, array &$data): void {
                foreach ($props as $property) {
                    if (!array_key_exists($property, $data)) {
                        continue;
                    }

                    $object->{$property} = $data[$property];
                    unset($data[$property]);
                }
            }, null, $class)($object, $props, $data);
        }
    }

    /**
     * Map private entity relations
     */
    private function setRelationProperties(array $properties, RelationMap $relMap, object $object, array &$data): void
    {
        $refl = new \ReflectionClass($object);

        foreach ($properties as $class => $props) {
            if ($class === '') {
                continue;
            }

            Closure::bind(static function (object $object, array $props, array &$data) use ($refl, $relMap): void {
                foreach ($props as $property) {
                    if (!\array_key_exists($property, $data)) {
                        continue;
                    }

                    $value = $data[$property];

                    if ($value instanceof ReferenceInterface) {
                        $prop = $refl->getProperty($property);

                        if ($prop->hasType()) {
                            /** @var \ReflectionNamedType[] $types */
                            $types = $prop->getType() instanceof \ReflectionUnionType
                                ? $prop->getType()->getTypes()
                                : [$prop->getType()];

                            foreach ($types as $type) {
                                $c = $type->getName();
                                if ($c === 'object' || $value instanceof $c) {
                                    $object->{$property} = $value;
                                    unset($data[$property]);

                                    // go to next property
                                    continue 2;
                                }
                            }

                            $relation = $relMap->getRelations()[$property] ?? null;
                            if ($relation !== null) {
                                $value = $relation->collect($relation->resolve($value, true));
                            }
                        }
                    }

                    $object->{$property} = $value;
                    unset($data[$property]);
                }
            }, null, $class)($object, $props, $data);
        }
    }
}
