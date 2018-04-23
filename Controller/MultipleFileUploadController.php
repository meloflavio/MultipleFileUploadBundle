<?php

namespace Liplex\MultipleFileUploadBundle\Controller;

use Liplex\MultipleFileUploadBundle\Repository\MultipleFileUploadRepository;
use Sonata\MediaBundle\Entity\BaseMedia;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Controller for the multiple image upload.
 */
class MultipleFileUploadController extends Controller
{
    /**
     * Multiple field upload action.
     *
     * @param Request $request Request
     *
     * @return JsonResponse
     */
    public function multipleImageUploadAction(Request $request)
    {
        $parameter = $request->request->all();

        $entityType = $parameter['entity'];
        $id = $parameter['id'];
        $field = $parameter['field'];

        $mediaManager = $this->get('sonata.media.manager.media');

        $configuration = $this->getEntityConfigurationFromMapping($entityType);

        /** @var MultipleFileUploadRepository $repository */
        $repository = $this->getDoctrine()->getRepository($configuration['class']);

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if ($file === null) {
            throw new BadRequestHttpException('Not all mandatory parameters are given');
        }

        $fieldConfiguration = $this->getFieldConfigurationForField($field, $configuration);

        $mediaContext = $fieldConfiguration['context'];

        $entity = $repository->find($id);
        if ($entity === null) {
            throw new NotFoundHttpException('The entity you requested can not be found.');
        }

        $mediaClass = $this->getParameter('liplex_multiple_file_upload.media_class');

        /** @var BaseMedia $media */
        $media = new $mediaClass();
        $media->setBinaryContent($file);
        $media->setContext($mediaContext);
        $media->setProviderName($fieldConfiguration['provider']);

        $mediaManager->save($media);

        $this->setMediaForField($entity, $field, $media);

        $repository->store($entity);

        $data = [
            'mediaId' => $media->getId(),
        ];

        return new JsonResponse($data);
    }

    /**
     * Get class from mapping.
     *
     * @param string $entityType
     *
     * @return array
     *
     * @throws BadRequestHttpException Thrown if no mapping available
     */
    private function getEntityConfigurationFromMapping($entityType)
    {
        /** @var array $mapping */
        $mapping = $this->getParameter('liplex_multiple_file_upload.mapping');

        foreach ($mapping as $entity) {
            $className = substr($entity['class'], strrpos($entity['class'], '\\') + 1);
            if ($className === $entityType) {
                return $entity;
            }
        }

        throw new BadRequestHttpException('Wrong mapping');
    }

    /**
     * Get field configuration from configuration.
     *
     * @param string $field
     * @param array  $configuration
     *
     * @return array
     *
     * @throws BadRequestHttpException Thrown if no context can be found in the configuration
     */
    private function getFieldConfigurationForField($field, array $configuration)
    {
        foreach ($configuration['fields'] as $fieldConfiguration) {
            if ($fieldConfiguration['field'] == $field) {
                return $fieldConfiguration;
            }
        }

        throw new BadRequestHttpException(sprintf('No context configuration found for the field %s', $field));
    }

    /**
     * Set media for field.
     *
     * @param object $entity
     * @param string $field
     * @param BaseMedia  $media
     *
     * @throws BadRequestHttpException Thrown if entity or field can not be found
     */
    private function setMediaForField($entity, $field, BaseMedia $media)
    {
        $getterFunction = 'get'.ucwords($field);
        $currentField = $entity->$getterFunction();

        $collectionClasses = [
            PersistentCollection::class,
            ArrayCollection::class,
        ];

        if (\in_array(get_class($currentField), $collectionClasses, true)) {
            $entity->$getterFunction()->add($media);
        } else {
            $setterFunction = 'set'.ucwords($field);
            $entity->$setterFunction($media);
        }
    }

    /**
     * Show image action.
     *
     * @param int $id ID
     *
     * @return BinaryFileResponse
     *
     * @throws NotFoundHttpException Thrown if image can not be found
     */
    public function showImageAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:Media');
        $imageProvider = $this->get('sonata.media.provider.image');

        $media = $repository->find($id);
        if ($media === null) {
            throw new NotFoundHttpException('The image you requested can not be found.');
        }

        $webPath = $this->get('kernel')->getRootDir().'/../web';

        $format = $imageProvider->getFormatName($media, 'image_upload_preview');
        $imagePath = $imageProvider->generatePublicUrl($media, $format);

        return new BinaryFileResponse($webPath.$imagePath);
    }

    /**
     * Get file name action.
     *
     * @param string $ids IDs
     *
     * @return JsonResponse
     *
     * @throws NotFoundHttpException Thrown if file can not be found
     */
    public function getFileNamesAction($ids)
    {
        $repository = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:Media');

        $ids = explode(',', $ids);

        $medias = $repository->findBy([
            'id' => $ids,
        ]);
        $mediaArray = [];
        /** @var BaseMedia $media */
        foreach ($medias as $media) {
            $mediaArray[$media->getId()] = $media->getName();
        }

        return new JsonResponse($mediaArray);
    }

    /**
     * Get file action.
     *
     * @param int $id ID
     *
     * @return BinaryFileResponse
     *
     * @throws NotFoundHttpException Thrown if file can not be found
     */
    public function getFileAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:Media');
        $fileProvider = $this->get('sonata.media.provider.file');

        /** @var BaseMedia $media */
        $media = $repository->find($id);
        if ($media === null) {
            throw new NotFoundHttpException('The file you requested can not be found.');
        }

        $webPath = $this->get('kernel')->getRootDir().'/../web';

        $filePath = $fileProvider->generatePublicUrl($media, 'reference');

        $response = new BinaryFileResponse($webPath.$filePath);
        $response->headers->set('Content-Type', $media->getContentType());
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$media->getName().'"');

        return $response;
    }

    /**
     * Delete media action.
     *
     * @param int $id ID
     *
     * @return Response
     *
     * @throws NotFoundHttpException Thrown if campaign can not be found or the area does not have a campaign image
     */
    public function deleteMediaAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:Media');
        $mediaManager = $this->get('sonata.media.manager.media');

        $media = $repository->find($id);
        if ($media === null) {
            throw new NotFoundHttpException('The media you requested can not be found.');
        }

        $mediaManager->delete($media);

        return new Response('Success');
    }
}
