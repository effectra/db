<?php

declare(strict_types=1);

namespace Effectra\Database;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Trait ModelEventTrait
 *
 * This trait provides event handling capabilities for a model.
 * 
 * @package Effectra\Database
 */
trait ModelEventTrait
{
    /**
     * @var array The array to store registered events.
     */
    private static array $events = [];

    /**
     * Get the event dispatcher instance.
     *
     * @return EventDispatcherInterface The event dispatcher instance.
     */
    public function eventDispatcher(): EventDispatcherInterface
    {
        return DB::getEventDispatcher();
    }

    /**
     * Add a new event to the list of registered events.
     *
     * @param string $name  The name of the event.
     * @param StoppableEventInterface $event The event instance.
     */
    public static function addEvent(string $name, StoppableEventInterface $event): void
    {
        static::$events[$name] = $event;
    }

    /**
     * Set the list of events.
     *
     * @param array $events The array of events.
     */
    public static function setEvents(array $events): void
    {
        static::$events = $events;
    }

    /**
     * Get the list of registered events.
     *
     * @return array The array of registered events.
     */
    public static function getEvents(): array
    {
        return static::$events;
    }

    /**
     * Get a specific event by name.
     *
     * @param string $name The name of the event.
     *
     * @return StoppableEventInterface|null The event instance, or null if not found.
     */
    public static function getEvent(string $name): ?StoppableEventInterface
    {
        return static::$events[$name] ?? null;
    }

    /**
     * Create a new event instance.
     *
     * @return StoppableEventInterface The newly created event instance.
     */
    public static function createEvent(): StoppableEventInterface
    {
        return new class implements StoppableEventInterface
        {
            private bool $propagationStopped = false;

            public function isPropagationStopped(): bool
            {
                return $this->propagationStopped;
            }

            public function stopPropagation(): void
            {
                $this->propagationStopped = true;
            }
        };
    }

    /**
     * Trigger an event by name.
     *
     * @param string $name The name of the event.
     *
     * @return bool True if the event is triggered successfully, false otherwise.
     */
    public function event(string $name): bool
    {
        $event = $this->getEvent($name);
        if ($event instanceof StoppableEventInterface) {
            $this->eventDispatcher()->dispatch($event);
        }
        $method = "on" . ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], []);
        }
        return true;
    }
}
