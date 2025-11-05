<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105164551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE restitution (id SERIAL NOT NULL, attribution_id INT DEFAULT NULL, employe_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, etat VARCHAR(255) NOT NULL, observation VARCHAR(255) DEFAULT NULL, check_list JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FA8209DEEB69F7B ON restitution (attribution_id)');
        $this->addSql('CREATE INDEX IDX_4FA8209D1B65292 ON restitution (employe_id)');
        $this->addSql('COMMENT ON COLUMN restitution.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE restitution ADD CONSTRAINT FK_4FA8209DEEB69F7B FOREIGN KEY (attribution_id) REFERENCES attributions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE restitution ADD CONSTRAINT FK_4FA8209D1B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE restitution DROP CONSTRAINT FK_4FA8209DEEB69F7B');
        $this->addSql('ALTER TABLE restitution DROP CONSTRAINT FK_4FA8209D1B65292');
        $this->addSql('DROP TABLE restitution');
    }
}
