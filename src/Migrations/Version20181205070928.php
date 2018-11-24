<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181205070928 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, thread_id INT DEFAULT NULL, message LONGTEXT NOT NULL, main_post TINYINT(1) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5A8A6C8DE2904019 (thread_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thread (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, first_post_id INT DEFAULT NULL, last_post_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, number_posts INT NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_31204C8312469DE2 (category_id), UNIQUE INDEX UNIQ_31204C8358056FD0 (first_post_id), UNIQUE INDEX UNIQ_31204C832D053F64 (last_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C8312469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C8358056FD0 FOREIGN KEY (first_post_id) REFERENCES post (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C832D053F64 FOREIGN KEY (last_post_id) REFERENCES post (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE category ADD last_active_thread_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1D23588C1 FOREIGN KEY (last_active_thread_id) REFERENCES thread (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_64C19C1D23588C1 ON category (last_active_thread_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C8358056FD0');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C832D053F64');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1D23588C1');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DE2904019');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE thread');
        $this->addSql('DROP INDEX IDX_64C19C1D23588C1 ON category');
        $this->addSql('ALTER TABLE category DROP last_active_thread_id');
    }
}
