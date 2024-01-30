<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240126020014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, image_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD user_image_id INT DEFAULT NULL, DROP image_name');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DD28C16D FOREIGN KEY (user_image_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649DD28C16D ON user (user_image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649DD28C16D');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP INDEX UNIQ_8D93D649DD28C16D ON user');
        $this->addSql('ALTER TABLE user ADD image_name VARCHAR(255) NOT NULL, DROP user_image_id');
    }
}
