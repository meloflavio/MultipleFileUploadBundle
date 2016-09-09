<?php

namespace Liplex\MultipleFileUploadBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Defines functions necessary for the multiple file upload.
 */
class MultipleFileUploadRepository extends EntityRepository
{
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
}
