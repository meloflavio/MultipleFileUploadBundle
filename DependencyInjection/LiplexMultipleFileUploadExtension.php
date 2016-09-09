<?php

namespace Liplex\Bundle\MultipleFileUploadBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 */
class LiplexMultipleFileUploadExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('liplex_multiple_file_upload.media_class', $config['media_class']);
        $container->setParameter('liplex_multiple_file_upload.mapping', $config['mapping']);
    }
}
