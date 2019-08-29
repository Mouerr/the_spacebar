<?php

namespace App\Twig;


use App\Service\MarkdownHelper;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private $markdownHelper;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(MarkdownHelper $markdownHelper,ContainerInterface $container)
    {
        $this->markdownHelper = $markdownHelper;
        $this->container = $container;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('cached_markdown', [$this, 'processMarkdown'], ['is_safe' => ['html']]),
        ];
    }


    public function processMarkdown($value)
    {
        return $this->container->get(MarkdownHelper::class)->parse($value);
        //return $this->markdownHelper->parse($value);
        //return strtoupper($value);
    }

    /**
     * Returns an array of service types required by such instances, optionally keyed by the service names used internally.
     *
     * For mandatory dependencies:
     *
     *  * array('logger' => 'Psr\Log\LoggerInterface') means the objects use the "logger" name
     *    internally to fetch a service which must implement Psr\Log\LoggerInterface.
     *  * array('Psr\Log\LoggerInterface') is a shortcut for
     *  * array('Psr\Log\LoggerInterface' => 'Psr\Log\LoggerInterface')
     *
     * otherwise:
     *
     *  * array('logger' => '?Psr\Log\LoggerInterface') denotes an optional dependency
     *  * array('?Psr\Log\LoggerInterface') is a shortcut for
     *  * array('Psr\Log\LoggerInterface' => '?Psr\Log\LoggerInterface')
     *
     * @return array The required service types, optionally keyed by service names
     */
    public static function getSubscribedServices()
    {
        return [
            MarkdownHelper::class,
        ];
    }
}
