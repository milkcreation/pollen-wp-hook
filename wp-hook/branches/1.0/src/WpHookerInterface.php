<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Pollen\Routing\RouteInterface;
use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;
use Pollen\WpPost\WpPostProxyInterface;

interface WpHookerInterface extends
    BootableTraitInterface,
    ConfigBagAwareTraitInterface,
    ContainerProxyInterface,
    WpPostProxyInterface
{
    /**
     * Ajout d'un couple nom d'accroche <> nom d'option d'enregistrement.
     *
     * @param string $hook
     * @param string $option
     * @param array $params
     *
     * @return static
     */
    public function addHookOption(string $hook, string $option, array $params = []): WpHookerInterface;

    /**
     * Chargement.
     *
     * @return static
     */
    public function boot(): WpHookerInterface;

    /**
     * Récupération de la liste des contenus d'accroche déclarés.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Récupération du contenu d'accroche.
     *
     * @param string $name
     *
     * @return WpHookableInterface|null
     */
    public function get(string $name): ?WpHookableInterface;

    /**
     * Récupération du contenu d'accroche selon l'identifiant de qualification du post Wordpress associé.
     *
     * @param int $id
     *
     * @return WpHookableInterface|null
     */
    public function getById(int $id): ?WpHookableInterface;

    /**
     * Récupération de la liste des noms de qualification des contenus d'accroche.
     *
     * @return array
     */
    public function getHookNames(): array;

    /**
     * Récupération de la liste des identifiants de qualification Wordpress (post_id) des contenus d'accroche.
     *
     * @return array
     */
    public function getIds(): array;

    /**
     * Récupération de la liste des chemins vers le post Wordpress des contenus d'accroche.
     *
     * @return array
     */
    public function getPaths(): array;

    /**
     * Récupération de l'instance de la route associée à un contenu d'accroche.
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getRoute(string $name): ?RouteInterface;

    /**
     * Récupération de l'instance du contenu d'accroche associé à une route.
     *
     * @param RouteInterface $route
     *
     * @return WpHookableInterface|null
     */
    public function getRouteHookable(RouteInterface $route): ?WpHookableInterface;

    /**
     * Vérification d'existence d'un identifiant de qualification dans la liste des contenus d'accroche déclaré.
     *
     * @param int $id
     *
     * @return bool
     */
    public function hasId(int $id): bool;

    /**
     * Vérification d'existence d'un chemin dans la liste des contenus d'accroche déclaré.
     *
     * @param string $path
     *
     * @return bool
     */
    public function hasPath(string $path): bool;

    /**
     * Enregistrement des infos de routage dans le hooks_bag
     *
     * @param mixed $value
     * @param string $option
     * @param mixed $original_value
     *
     * @return mixed
     */
    public function saveOption($value, string $option, $original_value);

    /**
     * Définition d'une route associé à une instance de contenu d'accroche.
     *
     * @param WpHookableInterface $hookable
     * @param RouteInterface $route
     *
     * @return static
     */
    public function setRoute(WpHookableInterface $hookable, RouteInterface $route): WpHookerInterface;

    /**
     * Enregistrement des valeurs du hooksBag en base de données.
     *
     * @return void
     */
    public function storeHooksBag(): void;
}
