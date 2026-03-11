<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [DB-03] Rimuove UNIQUE su bollo.vettura_id e aggiunge campi per storico e pagamento.
 *
 * I ~468 record esistenti non vengono modificati.
 * Avranno attiva = 1 di default.
 */
final class Version20260310001_Bollo_StoricoPagamento extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[DB-03] Bollo: rimuove UNIQUE su vettura_id, aggiunge attiva/importo/super_bollo/pagato/data_pagamento/created_at';
    }

    public function up(Schema $schema): void
    {
        // Controlla nome reale del UNIQUE index nel dump: UNIQ_131D5E43FC739189
        $this->addSql('ALTER TABLE bollo DROP INDEX IF EXISTS UNIQ_131D5E43FC739189');
        $this->addSql('ALTER TABLE bollo
            ADD COLUMN attiva       TINYINT(1)      NOT NULL DEFAULT 1     COMMENT "1 = bollo attivo/corrente",
            ADD COLUMN importo      DECIMAL(8,2)    NULL                   COMMENT "Importo bollo in euro",
            ADD COLUMN super_bollo  DECIMAL(8,2)    NULL                   COMMENT "Eventuale super-bollo in euro",
            ADD COLUMN pagato       TINYINT(1)      NOT NULL DEFAULT 0     COMMENT "1 = pagato",
            ADD COLUMN data_pagamento DATE          NULL,
            ADD COLUMN created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
        ');
        $this->addSql('ALTER TABLE bollo ADD INDEX idx_scad_bollo (data_scadenza_bollo)');
        $this->addSql('ALTER TABLE bollo ADD INDEX idx_bollo_attiva (attiva)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bollo
            DROP COLUMN attiva,
            DROP COLUMN importo,
            DROP COLUMN super_bollo,
            DROP COLUMN pagato,
            DROP COLUMN data_pagamento,
            DROP COLUMN created_at
        ');
        $this->addSql('ALTER TABLE bollo DROP INDEX IF EXISTS idx_scad_bollo');
        $this->addSql('ALTER TABLE bollo DROP INDEX IF EXISTS idx_bollo_attiva');
        $this->addSql('ALTER TABLE bollo ADD UNIQUE INDEX UNIQ_131D5E43FC739189 (vettura_id)');
    }
}
