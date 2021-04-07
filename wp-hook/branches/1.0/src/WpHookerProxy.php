<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use InvalidArgumentException;
use Pollen\Support\StaticProxy;
use RuntimeException;

/**
 * @see \Pollen\WpHook\WpHookerProxyInterface
 */
trait WpHookerProxy
{
    /**
     * Instance du gestionnaire de contenus d'accroche Wordpress.
     * @var WpHookerInterface
     */
    private $wpHooker;

    /**
     * Instance du gestionnaire de contenus d'accroche Wordpress|Instance d'un contenu d'accroche
     *
     * @param string|null $name
     *
     * @return WpHookerInterface|WpHookableInterface
     */
    public function wpHooker(?string $name = null)
    {
        if ($this->wpHooker === null) {
            try {
                $this->wpHooker = WpHooker::getInstance();
            } catch (RuntimeException $e) {
                $this->wpHooker = StaticProxy::getProxyInstance(
                    WpHookerInterface::class,
                    WpHooker::class,
                    method_exists($this, 'getContainer') ? $this->getContainer() : null
                );
            }
        }

        if ($name === null) {
            return $this->wpHooker;
        }

        if ($hookable = $this->wpHooker->get($name)) {
            return $hookable;
        }

        throw new InvalidArgumentException(sprintf('Hookable [%s] is unavailable', $name));
    }

    /**
     * Définition du gestionnaire de contenus d'accroche Wordpress.
     *
     * @param WpHookerInterface $wpHooker
     *
     * @return void
     */
    public function setWpHooker(WpHookerInterface $wpHooker): void
    {
        $this->wpHooker = $wpHooker;
    }
}
