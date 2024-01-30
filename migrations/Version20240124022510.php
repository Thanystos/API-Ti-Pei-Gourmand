<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240124022510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dish (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, category VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, availability TINYINT(1) NOT NULL, is_allergen TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dish_ingredient (id INT AUTO_INCREMENT NOT NULL, dish_id INT DEFAULT NULL, ingredient_id INT DEFAULT NULL, INDEX IDX_77196056148EB0CB (dish_id), INDEX IDX_77196056933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, is_allergen TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingredient_dish (ingredient_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_CB2C3C2E933FE08C (ingredient_id), INDEX IDX_CB2C3C2E148EB0CB (dish_id), PRIMARY KEY(ingredient_id, dish_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dish_ingredient ADD CONSTRAINT FK_77196056148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE dish_ingredient ADD CONSTRAINT FK_77196056933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
        $this->addSql('ALTER TABLE ingredient_dish ADD CONSTRAINT FK_CB2C3C2E933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_dish ADD CONSTRAINT FK_CB2C3C2E148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish_ingredient DROP FOREIGN KEY FK_77196056148EB0CB');
        $this->addSql('ALTER TABLE dish_ingredient DROP FOREIGN KEY FK_77196056933FE08C');
        $this->addSql('ALTER TABLE ingredient_dish DROP FOREIGN KEY FK_CB2C3C2E933FE08C');
        $this->addSql('ALTER TABLE ingredient_dish DROP FOREIGN KEY FK_CB2C3C2E148EB0CB');
        $this->addSql('DROP TABLE dish');
        $this->addSql('DROP TABLE dish_ingredient');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE ingredient_dish');
    }
}
