<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181223140654 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE post ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DB03A8386 ON post (created_by_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D896DBBDE ON post (updated_by_id)');
        $this->addSql('ALTER TABLE thread ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_31204C83B03A8386 ON thread (created_by_id)');
        $this->addSql('CREATE INDEX IDX_31204C83896DBBDE ON thread (updated_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DB03A8386');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D896DBBDE');
        $this->addSql('DROP INDEX IDX_5A8A6C8DB03A8386 ON post');
        $this->addSql('DROP INDEX IDX_5A8A6C8D896DBBDE ON post');
        $this->addSql('ALTER TABLE post ADD created_by VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD updated_by VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83B03A8386');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83896DBBDE');
        $this->addSql('DROP INDEX IDX_31204C83B03A8386 ON thread');
        $this->addSql('DROP INDEX IDX_31204C83896DBBDE ON thread');
        $this->addSql('ALTER TABLE thread ADD created_by VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD updated_by VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP created_by_id, DROP updated_by_id');
    }
}
