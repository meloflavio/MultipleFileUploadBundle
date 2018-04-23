<?php

namespace Liplex\MultipleFileUploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom type for multiple file upload with angular form.
 */
class MultipleFileUploadType extends AbstractType
{
    /**
     * Add custom option for single upload.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'read_only' => false,
            'single_upload' => false,
            'empty_data' => null,
            'allow_images' => false,
            'allow_files' => false,
            'max_file_size' => 5,
            'image_extensions' => [
                'jpg',
                'png',
                'jpeg',
            ],
            'file_extensions' => [
                'pdf',
                'doc',
                'docx',
                'xls',
                'xlsx',
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $allowedExtensions = [];
        $allowedExtensionsFilter = '|';
        if ($options['allow_images']) {
            $allowedExtensions = array_merge($allowedExtensions, $options['image_extensions']);
            foreach ($options['image_extensions'] as $option) {
                $allowedExtensionsFilter .= $option.'|';
            }
        }
        if ($options['allow_files']) {
            $allowedExtensions = array_merge($allowedExtensions, $options['file_extensions']);
            foreach ($options['file_extensions'] as $option) {
                $allowedExtensionsFilter .= $option.'|';
            }
        }

        $view->vars = array_merge($view->vars, [
            'single_upload' => $options['single_upload'],
            'allow_images' => $options['allow_images'],
            'allow_files' => $options['allow_files'],
            'allowed_extensions' => $allowedExtensions,
            'allowed_extensions_filter' => $allowedExtensionsFilter,
            'max_file_size' => $options['max_file_size'],
            'read_only' => $options['read_only']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'multiple_file_upload';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
