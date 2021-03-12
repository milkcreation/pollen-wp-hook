<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Pollen\WpPost\WpPostQueryInterface;
use Pollen\WpPost\WpPostProxy;
use WP_Post;

class WpHookable implements WpHookableInterface
{
    use WpHookerProxy;
    use WpPostProxy;

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
     * @param int $id
     * @param string $path
     * @param WpHookerInterface|null $wpHooker
     */
    public function __construct(int $id, string $path, ?WpHookerInterface $wpHooker = null)
    {
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
    public function getWpPost(): ?WP_Post
    {
        if ($this->wpPost === null) {
            $this->wpPost =  ($post = $this->getPost()) ? $post->getWpPost() : null;
        }

        return $this->wpPost;
    }
}
