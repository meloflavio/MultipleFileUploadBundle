# Multiple File Upload

## AppKernel

```
$bundles = [
    ...
    new Liplex\MultipleFileUploadBundle\LiplexMultipleFileUploadBundle(),
];
```

## Configuration

Each field that wants to use the multiple file upload has to be configured.

```
liplex_multiple_file_upload:
        directory: "%liplex_multiple_file_upload_path%" // Default is set to folder web
    media_class: 'Application\Sonata\MediaBundle\Entity\Media'
    mapping:
        product:
            class: 'Company\Bundle\AppBundle\Entity\Product'
            fields:
                image:
                    field: image
                    context: product.image
                    provider: sonata.media.provider.image
                files:
                    field: files
                    context: product.files
                    provider: sonata.media.provider.file
        ...
```

## Field usage

```
// Multiple images
->add('images', 'multiple_file_upload', [
    'label' => 'label.images',
    'allow_images' => true,
])

// Multiple files
->add('files', 'multiple_file_upload', [
    'label' => 'label.files',
    'allow_files' => true,
])

// Set maximal file size to 20 MB
->add('files', 'multiple_file_upload', [
    'label' => 'label.files',
    'max_file_size' => 20,
    'allow_files' => true,
])

// Single upload
->add('image', 'multiple_file_upload', [
    'label' => 'label.image',
    'single_upload' => true,
    'allow_images' => true,
])

// Define extensions
->add('file', 'multiple_file_upload', [
    'label' => 'label.file',
    'single_upload' => true,
    'allow_files' => true,
    'allowed_extensions' => [
        'pdf',
        'md'
    ]
])
```

## Prerequisites

There are a few prerequisites for the bundle to work properly.

### Additional configuration

You have to set those templates in the `sonata_doctrine_orm_admin` configuration.

```
sonata_doctrine_orm_admin:
    templates:
        form:
            - 'LiplexMultipleFileUploadBundle:Admin/Form:form_fields.html.twig'
        types:
            show:
                multiple_image_view: 'LiplexMultipleFileUploadBundle:Admin/CRUD:show_multiple_image_view.html.twig'
                multiple_file_view: 'LiplexMultipleFileUploadBundle:Admin/CRUD:show_multiple_file_view.html.twig'
```

### Routing

```
liplex_multiple_file_upload:
    resource: "@LiplexMultipleFileUploadBundle/Resources/config/routing.yml"
    prefix:   /
```

### Standard layout

Add the following overwrites for the `standard_layout.html.twig` file.

```
{% block stylesheets %}
    {{  parent() }}
    <link rel="stylesheet" href="{{ asset('bundles/liplexmultiplefileupload/css/style.css') }}" type="text/css" media="all" />
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="{{ asset('bundles/liplexmultiplefileupload/js/vendor/angular.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/liplexmultiplefileupload/js/vendor/angular-file-upload.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/liplexmultiplefileupload/js/vendor/ui-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/liplexmultiplefileupload/js/multiple-file-upload/app.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/liplexmultiplefileupload/js/multiple-file-upload/controllers.js') }}" type="text/javascript"></script>
{% endblock %}

{% block body_attributes %}ng-app="{% block angularApp %}app{% endblock %}"{% endblock %}
```

### Media

As you have to generate your own media entities with the sonata media bundle there is no generic one which can be used 
for the generation of the media entities. Therefore you have to set your custom class in the configuration.

### Repositories

Repositories have to extend the `MultipleFileUploadRepository` repository...

```
use Liplex\MultipleFileUploadBundle\Repository\MultipleFileUploadRepository;

class YourRepository extends MultipleFileUploadRepository
```

... or have to implement the `store` method themselves.

```
/**
 * Store one entity.
 *
 * @param mixed $entity
 */
public function store($entity)
{
    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush($entity);
}
```
