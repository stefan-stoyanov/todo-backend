<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230219070742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE todo_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE todo (id INT NOT NULL, notes_id INT NOT NULL, text VARCHAR(255) NOT NULL, checked BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A0EB6A0FC56F556 ON todo (notes_id)');
        $this->addSql('ALTER TABLE todo ADD CONSTRAINT FK_5A0EB6A0FC56F556 FOREIGN KEY (notes_id) REFERENCES notes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE todo_id_seq CASCADE');
        $this->addSql('ALTER TABLE todo DROP CONSTRAINT FK_5A0EB6A0FC56F556');
        $this->addSql('DROP TABLE todo');
    }
}
