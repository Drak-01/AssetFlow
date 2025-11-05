<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105014237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attributions (id SERIAL NOT NULL, utilisateur_id INT NOT NULL, actif_id INT NOT NULL, quantite INT NOT NULL, date_attribution TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_fin TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, statut VARCHAR(20) NOT NULL, notes TEXT DEFAULT NULL, assigne_par VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_14C967D2FB88E14F ON attributions (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_14C967D240E1C722 ON attributions (actif_id)');
        $this->addSql('COMMENT ON COLUMN attributions.date_attribution IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN attributions.date_fin IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN attributions.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN attributions.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE attributions ADD CONSTRAINT FK_14C967D2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE attributions ADD CONSTRAINT FK_14C967D240E1C722 FOREIGN KEY (actif_id) REFERENCES actifs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE attributions DROP CONSTRAINT FK_14C967D2FB88E14F');
        $this->addSql('ALTER TABLE attributions DROP CONSTRAINT FK_14C967D240E1C722');
        $this->addSql('DROP TABLE attributions');
    }
}
