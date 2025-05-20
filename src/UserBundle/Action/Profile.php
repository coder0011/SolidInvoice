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

namespace SolidInvoice\UserBundle\Action;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class Profile
{
    /**
     * @return array<never>
     */
    #[Template('@SolidInvoiceUser/Profile/show.html.twig')]
    public function __invoke(TokenStorageInterface $storage): array
    {
        return [];
    }
}
