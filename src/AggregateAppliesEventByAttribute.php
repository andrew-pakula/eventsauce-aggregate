<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Aggregate;

use Andreo\EventSauce\Aggregate\Exception\InvalidArgumentException;
use ReflectionObject;

trait AggregateAppliesEventByAttribute
{
    private int $aggregateRootVersion = 0;

    protected function apply(object $event): void
    {
        $reflection = new ReflectionObject($this);

        foreach ($reflection->getMethods() as $method) {
            $attribute = $method->getAttributes(EventSourcingHandler::class)[0] ?? null;
            if (null === $attribute) {
                continue;
            }
            if (1 !== $method->getNumberOfRequiredParameters()) {
                throw InvalidArgumentException::eventHandlerOneArgument($method->getShortName());
            }

            $parameter = $method->getParameters()[0];
            if (null === $type = $parameter->getType()) {
                throw InvalidArgumentException::eventHandlerTypeArgument($method->getShortName());
            }

            if ($event::class === $type->getName()) {
                $method->invoke($this, $event);
                ++$this->aggregateRootVersion;

                return;
            }
        }
    }
}
