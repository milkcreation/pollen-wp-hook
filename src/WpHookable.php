<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Pollen\Routing\RouteInterface;
use Pollen\Support\Concerns\ParamsBagAwareTrait;
use Pollen\WpPost\WpPostQueryInterface;
use Pollen\WpPost\WpPostProxy;
use WP_Post;

class WpHookable implements WpHookableInterface
{
    use ParamsBagAwareTrait;
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
     * Liste des paramètres par défaut.
     *
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            /**
             * Intitulé de qualification.
             * @var string $label
             */
            'label'     => $this->getName(),
            /**
             * Indicateur dans la liste des posts de l'interface d'administration Wordpress.
             * @var bool|string $post_state
             */
            'post_state' => true,
            /**
             * Message de notification de l'édition du post de l'interface d'administration Wordpress.
             * @var bool|string $post_state
             */
            'edit_notice' => true
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEditNotice(): string
    {
        if ($editNotice = $this->params('edit_notice')) {
            return is_string($editNotice)
                ? $editNotice : sprintf('Vous éditez la page de contenu : %s', $this->getLabel());
        }
        return '';
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
    public function getLabel(): string
    {
        return $this->params('label', '');
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
    public function getPostState(): string
    {
        if ($postState = $this->params('post_state')) {
            return is_string($postState)
                ? $postState : sprintf('Page de contenu : %s', $this->getLabel());
        }
        return '';
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
