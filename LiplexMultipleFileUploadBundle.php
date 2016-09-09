<?php

namespace Liplex\Bundle\MultipleFileUploadBundle;

use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Register multiple file upload type to bundle.
 */
class LiplexMultipleFileUploadBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $this->registerFormMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping([
            'multiple_file_upload' => 'Liplex\Bundle\MultipleFileUploadBundle\Form\Type\MultipleFileUploadType',
        ]);
    }
}
