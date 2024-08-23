<?php

namespace Telegram\Bot\Traits;

use Psr\Container\ContainerInterface;

/**
 * HasContainer.
 */
trait HasContainer
{
    /** @var ContainerInterface|null IoC Container */
    protected ?ContainerInterface $container = null;

    /**
     * Determine if IoC Container is set.
     */
    public function hasContainer(): bool
    {
        return $this->container !== null;
    }

    /**
     * Get the IoC Container.
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container ?? \Illuminate\Container\Container::getInstance();
    }

    /**
     * Set the IoC Container.
     *
     * @param  ?ContainerInterface  $container instance
     * @return $this
     */
    public function setContainer(?ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
