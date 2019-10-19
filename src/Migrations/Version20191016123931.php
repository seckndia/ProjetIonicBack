<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191016123931 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE profil (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarifs (id INT AUTO_INCREMENT NOT NULL, borne_inferieure INT NOT NULL, borne_superieure INT NOT NULL, valeur INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compts (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE retrait (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, retrait_id INT DEFAULT NULL, envoie_id INT DEFAULT NULL, tarif_id INT DEFAULT NULL, cni VARCHAR(255) DEFAULT NULL, montant BIGINT DEFAULT NULL, date_envoie DATETIME DEFAULT NULL, code_envoie VARCHAR(255) NOT NULL, date_retrait DATETIME DEFAULT NULL, commission_etat BIGINT NOT NULL, commission_admin BIGINT NOT NULL, commission_retrait BIGINT NOT NULL, commission_envoie BIGINT NOT NULL, status VARCHAR(255) NOT NULL, agence VARCHAR(255) NOT NULL, INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D17EF8457A (retrait_id), INDEX IDX_723705D1425C347D (envoie_id), INDEX IDX_723705D1357C0A59 (tarif_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depots (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partenaire (id INT AUTO_INCREMENT NOT NULL, entreprise VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, tel INT NOT NULL, status VARCHAR(255) NOT NULL, ninea VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_32FFA373C678AEBE (ninea), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE envoie (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D17EF8457A FOREIGN KEY (retrait_id) REFERENCES retrait (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1425C347D FOREIGN KEY (envoie_id) REFERENCES envoie (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1357C0A59 FOREIGN KEY (tarif_id) REFERENCES tarifs (id)');
        $this->addSql('ALTER TABLE user ADD partenaire_id INT DEFAULT NULL, ADD num_compt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64998DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AF4ECA59 FOREIGN KEY (num_compt_id) REFERENCES compts (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64998DE13AC ON user (partenaire_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649AF4ECA59 ON user (num_compt_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1357C0A59');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AF4ECA59');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D17EF8457A');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64998DE13AC');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1425C347D');
        $this->addSql('DROP TABLE profil');
        $this->addSql('DROP TABLE tarifs');
        $this->addSql('DROP TABLE compts');
        $this->addSql('DROP TABLE retrait');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE depots');
        $this->addSql('DROP TABLE partenaire');
        $this->addSql('DROP TABLE envoie');
        $this->addSql('DROP INDEX IDX_8D93D64998DE13AC ON user');
        $this->addSql('DROP INDEX IDX_8D93D649AF4ECA59 ON user');
        $this->addSql('ALTER TABLE user DROP partenaire_id, DROP num_compt_id');
    }
}
