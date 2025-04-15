<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('env(SOLIDINVOICE_DATABASE_URL)', 'sqlite:///%env(SOLIDINVOICE_CONFIG_DIR)%/db/solidinvoice.db');
    $parameters->set('env(SOLIDINVOICE_LOCALE)', 'en');
    $parameters->set('env(SOLIDINVOICE_APP_SECRET)', bin2hex(random_bytes(12)));
    $parameters->set('env(SOLIDINVOICE_INSTALLED)', null);
    $parameters->set('env(SOLIDINVOICE_ALLOW_REGISTRATION)', '0');

    $parameters->set('env(SOLIDINVOICE_SENTRY_DSN)', null);
    $parameters->set('env(SOLIDINVOICE_SENTRY_RELEASE)', '');
    $parameters->set('env(SOLIDINVOICE_SENTRY_SEND_DEFAULT_PII)', '0');
    $parameters->set('env(SOLIDINVOICE_MAILER_DSN)', 'null://null');

    $parameters->set('env(SOLIDINVOICE_CONFIG_DIR)', param('kernel.project_dir') . '/config/env');

    $containerConfigurator->services()
        ->set(Monolog\Processor\PsrLogMessageProcessor::class)
        ->tag('monolog.processor', ['handler' => 'sentry']);

    $services = $containerConfigurator->services();

    $services->alias(StimulusHelper::class, 'stimulus.helper');
};
