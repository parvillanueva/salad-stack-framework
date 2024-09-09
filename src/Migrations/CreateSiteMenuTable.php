<?php
class CreateSiteMenuTable
{
    public function up(PDO $pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_menu (
            id INT AUTO_INCREMENT PRIMARY KEY,
            menu VARCHAR(255) NOT NULL,
            page_id INT DEFAULT NULL,
            parent_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (page_id) REFERENCES site_pages(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES site_menu(id) ON DELETE CASCADE
        )");
    }

    public function down(PDO $pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS site_menu");
    }
}
