<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [AUTH-01] Crea tabella user nativa Symfony Security.
 * Sostituisce fos_user (FOSUserBundle deprecato).
 *
 * NOTA: Gli hash bcrypt esistenti nella tabella fos_user sono
 * compatibili con Symfony Security (stesso algoritmo).
 * Migrare manualmente i 3 utenti esistenti dopo questa migration.
 */
final class Version20260310004_User_Create extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[AUTH-01] Crea tabella user per Symfony Security nativo (sostituisce fos_user)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id        INT AUTO_INCREMENT NOT NULL,
            email     VARCHAR(180)  NOT NULL,
            roles     JSON          NOT NULL,
            password  VARCHAR(255)  NOT NULL,
            nome      VARCHAR(100)  NULL,
            cognome   VARCHAR(100)  NULL,
            is_active TINYINT(1)    NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('-- ISTRUZIONE MANUALE: inserire qui gli utenti migrati da fos_user');
        $this->addSql('-- INSERT INTO user (email, roles, password, nome, cognome) VALUES (...)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `user`');
    }
}
