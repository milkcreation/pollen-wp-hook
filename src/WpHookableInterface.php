<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Pollen\Routing\RouteInterface;
use Pollen\WpPost\WpPostProxyInterface;
use Pollen\WpPost\WpPostQueryInterface;
use WP_Post;

interface WpHookableInterface extends WpHookerProxyInterface, WpPostProxyInterface
{
    /**
     * Récupération de l'identifiant de qualification du post.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Récupération du nom de qualification.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Récupération du chemin de l'url du post.
     *
     * @return string
     */
    public function getPath(): string;


    /**
     * Récupération de l'instance du post associé.
     *
     * @return WpPostQueryInterface|null
     */
    public function getPost(): ?WpPostQueryInterface;

    /**
     * Récupération de l'instance du post Wordpress associé.
     *
     * @return WP_Post|null
     */
    public function getWpPost(): ?WP_Post;

    /**
     * Récupération de la route associée.
     *
     * @return RouteInterface
     */
    public function getRoute(): ?RouteInterface;

    /**
     * Définition de la route associée.
     *
     * @param RouteInterface $route
     *
     * @return WpHookableInterface
     */
    public function setRoute(RouteInterface $route): WpHookableInterface;
}
