<?php

declare(strict_types=1);

namespace Pollen\WpHook;

interface WpHookerProxyInterface
{
    /**
     * Instance du gestionnaire de contenus d'accroche Wordpress.
     *
     * @return WpHookerInterface
     */
    public function wpHooker(): WpHookerInterface;

    /**
     * Définition du gestionnaire de contenus d'accroche Wordpress.
     *
     * @param WpHookerInterface $wpHooker
     *
     * @return void
     */
    public function setWpHooker(WpHookerInterface $wpHooker): void;
}
