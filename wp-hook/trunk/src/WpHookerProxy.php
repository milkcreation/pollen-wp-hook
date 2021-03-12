<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Psr\Container\ContainerInterface as Container;
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
     * Instance du gestionnaire de contenus d'accroche Wordpress.
     *
     * @return WpHookerInterface
     */
    public function wpHooker(): WpHookerInterface
    {
        if ($this->wpHooker === null) {
            $container = method_exists($this, 'getContainer') ? $this->getContainer() : null;

            if ($container instanceof Container && $container->has(WpHookerInterface::class)) {
                $this->wpHooker = $container->get(WpHookerInterface::class);
            } else {
                try {
                    $this->wpHooker = WpHooker::getInstance();
                } catch(RuntimeException $e) {
                    $this->wpHooker = new WpHooker();
                }
            }
        }

        return $this->wpHooker;
    }

    /**
     * Définition du gestionnaire de contenus d'accroche Wordpress.
     *
     * @param WpHookerInterface $wpHooker
     *
     * @return static
     */
    public function setWpHooker(WpHookerInterface $wpHooker): self
    {
        $this->wpHooker = $wpHooker;

        return $this;
    }
}
