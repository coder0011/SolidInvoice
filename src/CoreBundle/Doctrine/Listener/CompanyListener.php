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

namespace SolidInvoice\CoreBundle\Doctrine\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use SolidInvoice\CoreBundle\Company\CompanySelector;
use SolidInvoice\CoreBundle\Entity\Company;
use Symfony\Component\Uid\Ulid;

#[AsDoctrineListener(Events::prePersist)]
final class CompanyListener
{
    public function __construct(
        private readonly CompanySelector $companySelector,
    ) {
    }

    /**
     * Maps additional metadata.
     */
    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();

        $em = $eventArgs->getObjectManager();
        $metaData = $em->getClassMetadata($object::class);

        if ($metaData->hasAssociation('company')) {
            $repository = $em->getRepository(Company::class);
            $companyId = $this->companySelector->getCompany();

            if ($companyId instanceof Ulid) {
                $object->setCompany($repository->find($companyId));
            }
        }
    }
}
