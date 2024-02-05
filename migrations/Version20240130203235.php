<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240130203235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE display_preference (id INT AUTO_INCREMENT NOT NULL, displayed_columns JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD display_preference_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649529DF284 FOREIGN KEY (display_preference_id) REFERENCES display_preference (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649529DF284 ON user (display_preference_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649529DF284');
        $this->addSql('DROP TABLE display_preference');
        $this->addSql('DROP INDEX UNIQ_8D93D649529DF284 ON user');
        $this->addSql('ALTER TABLE user DROP display_preference_id');
    }
}
