<?php
class CreateSitePagesTable
{
    public function up(PDO $pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_pages(
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            status INT DEFAULT 0,
            is_home INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS site_page_content(
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_id INT,
            section TEXT,
            status INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (page_id) REFERENCES site_pages(id) ON DELETE CASCADE
        )");
    }

    public function down(PDO $pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS site_page_content");
        $pdo->exec("DROP TABLE IF EXISTS site_pages");
    }
}
