<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Bundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WilliamRijksen\AzureMessengerAdapter\Bundle\DependencyInjection\Compiler\AzureAdapterPass;

final class AzureMessengerAdapterBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new AzureAdapterPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }
}
