<?php

declare(strict_types=1);

namespace Pollen\WpHook;

interface WpHookerProxyInterface
{
    /**
     * Instance du gestionnaire de contenus d'accroche Wordpress|Instance d'un contenu d'accroche
     *
     * @param string|null $name
     *
     * @return WpHookerInterface|WpHookableInterface|null
     */
    public function wpHooker(?string $name = null);

    /**
     * Définition du gestionnaire de contenus d'accroche Wordpress.
     *
     * @param WpHookerInterface $wpHooker
     *
     * @return void
     */
    public function setWpHooker(WpHookerInterface $wpHooker): void;
}
