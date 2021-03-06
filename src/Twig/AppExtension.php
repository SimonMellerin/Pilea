<?php

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

final class AppExtension extends AbstractExtension
{
    /** @var ContainerBagInterface */
    private $parameters;

    /**
     * Default constructor
     */
    public function __construct(ContainerBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('pilea_version', [$this, 'getVersion']),
            new TwigFunction('pilea_repo_git', [$this, 'getGitRepo']),
            new TwigFunction('pilea_documentation', [$this, 'getDocumentation']),
            new TwigFunction('pilea_help_icon_link', [$this, 'getHelpIconLink']),
            new TwigFunction('pilea_help_graph', [$this, 'getGraphHelp']),
            new TwigFunction('pilea_user_max_places', [$this, 'getUserMaxPlaces']),
            new TwigFunction('pilea_user_can_share_place', [$this, 'canUserSharePlace']),
            new TwigFunction('pilea_user_can_fetch', [$this, 'canUserFetchData']),
            new TwigFunction('pilea_user_can_export', [$this, 'canUserExportData']),
            new TwigFunction('pilea_place_can_be_public', [$this, 'canPlaceBePublic']),
            new TwigFunction('pilea_user_can_add_place', [$this, 'canUserAddPlace']),
        ];
    }

    public function getVersion(): string
    {
        return $this->parameters->get('pilea.version');
    }

    public function getGitRepo(): string
    {
        return $this->parameters->get('pilea.repo_git');
    }

    public function getDocumentation(?string $path = ''): string
    {
        $documentationBaseUri = $this->parameters->get('pilea.documentation');
        $version = $this->parameters->get('pilea.version');
        return \sprintf("%s%s/%s", $documentationBaseUri, $version, $path);
    }

    public function getHelpIconLink(?string $path, ?string $title = null, ?string $class = null): string
    {
        $link = self::getDocumentation(\sprintf("%s", $path));
        return \sprintf(
            '<a href="%s" target="_blank" class="%s" title="%s"><i class="fas fa-question-circle"></i></a>',
            $link,
            $class,
            $title
        );
    }

    public function getGraphHelp(?string $graph): string
    {
        $path = \sprintf("utilisateur/graph.html#%s", $graph);
        return self::getHelpIconLink($path, "Comment lire ce graphique ?", "help");
    }

    public function getUserMaxPlaces(): ?int
    {
        $userMaxPlaces = (int)$this->parameters->get('pilea.user.max_places');

        return $userMaxPlaces === -1 ? null : $userMaxPlaces;
    }

    public function canUserSharePlace(): bool
    {
        return (bool)$this->parameters->get('pilea.user.can_share_place');
    }

    public function canUserFetchData(): bool
    {
        return (bool)$this->parameters->get('pilea.user.can_fetch');
    }

    public function canUserExportData(): bool
    {
        return (bool)$this->parameters->get('pilea.user.can_export');
    }

    public function canPlaceBePublic(): bool
    {
        return (bool)$this->parameters->get('pilea.place_can_be_public');
    }

    public function canUserAddPlace(User $user): bool
    {
        if ($userMaxPlaces = self::getUserMaxPlaces()) {
            return \count($user->getPlaces()) < $userMaxPlaces;
        }

        return true;
    }
}
