<?php
class CreateSiteContentTable
{
    public function up(PDO $pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_contents(
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function down(PDO $pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS site_contents");
    }
}
