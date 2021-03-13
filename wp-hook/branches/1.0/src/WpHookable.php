<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Pollen\Routing\RouteInterface;
use Pollen\WpPost\WpPostQueryInterface;
use Pollen\WpPost\WpPostProxy;
use WP_Post;

class WpHookable implements WpHookableInterface
{
    use WpHookerProxy;
    use WpPostProxy;

    /**
     * Nom de qualification.
     * @var string
     */
    private $name;

    /**
     * Identifiant de qualification du post associé.
     * @var int
     */
    private $id;

    /**
     * Chemin vers le post.
     * @var string
     */
    private $path;

    /**
     * Instance du post associé.
     * @var WpPostQueryInterface
     */
    protected $post;

    /**
     * Instance du post Wordpress associé.
     * @var WP_Post
     */
    protected $wpPost;

    /**
     * @param string $name
     * @param int $id
     * @param string $path
     * @param WpHookerInterface|null $wpHooker
     */
    public function __construct(string $name, int $id, string $path, ?WpHookerInterface $wpHooker = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->path = $path;

        if ($wpHooker !== null) {
            $this->setWpHooker($wpHooker);
        }
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getPost(): ?WpPostQueryInterface
    {
        if ($this->post === null) {
            $this->post = $this->WpPost()->post($this->id);
        }

        return $this->post;
    }

    /**
     * @inheritDoc
     */
    public function getRoute(): ?RouteInterface
    {
        return $this->wpHooker()->getRoute($this->getName());
    }

    /**
     * @inheritDoc
     */
    public function getWpPost(): ?WP_Post
    {
        if ($this->wpPost === null) {
            $this->wpPost =  ($post = $this->getPost()) ? $post->getWpPost() : null;
        }

        return $this->wpPost;
    }

    /**
     * @inheritDoc
     */
    public function setRoute(RouteInterface $route): WpHookableInterface
    {
        $this->wpHooker()->setRoute($this, $route);

        return $this;
    }
}
