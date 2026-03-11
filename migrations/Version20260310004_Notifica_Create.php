<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [DB-04] Crea la tabella notifica per il tracciamento degli avvisi ai clienti.
 *
 * Dipende da: Version20260310003_User_Create (tabella user deve esistere)
 */
final class Version20260310004_Notifica_Create extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[DB-04] Crea tabella notifica per tracciamento avvisi scadenze';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notifica (
            id              INT AUTO_INCREMENT NOT NULL,
            anagrafica_id   INT NOT NULL,
            vettura_id      INT NULL,
            utente_id       INT NULL COMMENT "Utente che ha effettuato la chiamata",
            tipo_scadenza   VARCHAR(30) NOT NULL COMMENT "revisione|assicurazione|bollo|patente",
            canale          VARCHAR(20) NOT NULL COMMENT "telefono|sms|email|whatsapp",
            data_invio      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            esito           VARCHAR(30) NULL COMMENT "inviata|non_risponde|rinnovato|non_interessato",
            note            VARCHAR(255) NULL,
            PRIMARY KEY (id),
            INDEX idx_notifica_anagrafica (anagrafica_id),
            INDEX idx_notifica_vettura (vettura_id),
            INDEX idx_notifica_utente (utente_id),
            INDEX idx_notifica_data (data_invio),
            INDEX idx_notifica_tipo (tipo_scadenza),
            CONSTRAINT fk_notifica_anagrafica FOREIGN KEY (anagrafica_id) REFERENCES anagrafica (id) ON DELETE CASCADE,
            CONSTRAINT fk_notifica_vettura    FOREIGN KEY (vettura_id)    REFERENCES vettura (id)    ON DELETE SET NULL,
            CONSTRAINT fk_notifica_utente     FOREIGN KEY (utente_id)     REFERENCES `user` (id)     ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notifica');
    }
}
