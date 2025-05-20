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

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20400_3 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $usersTable = $schema->getTable('users');
        $usersTable->addColumn('google_id', 'string', [
            'notnull' => false,
            'length' => 45,
        ]);
        $usersTable->addIndex(['google_id']);
    }

    public function down(Schema $schema): void
    {
        $usersTable = $schema->getTable('users');
        $usersTable->dropColumn('google_id');
    }
}
