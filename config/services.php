<?php

declare(strict_types=1);

use Codefog\FaqTagsBundle\FaqManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->bind('$tagsManager', service('codefog_tags.manager.codefog_faq'))
    ;

    $services->load('Codefog\\FaqTagsBundle\\', __DIR__.'/../src')
        ->exclude([__DIR__.'/../src/FrontendModule'])
    ;

    $services
        ->set(FaqManager::class)
        ->public()
    ;
};
