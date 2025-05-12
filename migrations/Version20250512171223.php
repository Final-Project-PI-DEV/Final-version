<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512171223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, user_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_B6BD307F2D6BA2D9 (reclamation_id), INDEX IDX_B6BD307FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F2D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY fk_user_abonnement');
        $this->addSql('DROP TABLE personne');
        $this->addSql('DROP TABLE abonnement');
        $this->addSql('ALTER TABLE comment DROP typing_status');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY fk_materiel_loisir');
        $this->addSql('DROP INDEX fk_materiel_loisir ON materiel');
        $this->addSql('ALTER TABLE materiel DROP loisir_id');
        $this->addSql('ALTER TABLE user DROP face_encoding, CHANGE password password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages DROP reclamation_id, DROP sender_id, DROP receiver_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personne (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE abonnement (abonnement_id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, status VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type_abonnement VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, prix NUMERIC(10, 2) DEFAULT NULL, INDEX fk_user_abonnement (user_id), PRIMARY KEY(abonnement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT fk_user_abonnement FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F2D6BA2D9');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA76ED395');
        $this->addSql('DROP TABLE message');
        $this->addSql('ALTER TABLE comment ADD typing_status VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE materiel ADD loisir_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT fk_materiel_loisir FOREIGN KEY (loisir_id) REFERENCES loisir (id)');
        $this->addSql('CREATE INDEX fk_materiel_loisir ON materiel (loisir_id)');
        $this->addSql('ALTER TABLE messenger_messages ADD reclamation_id INT DEFAULT 0 NOT NULL, ADD sender_id INT DEFAULT 0 NOT NULL, ADD receiver_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user ADD face_encoding LONGBLOB DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
    }
}
