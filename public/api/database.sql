-- Structure de la table users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cuisine', 'direction') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table diaporama
CREATE TABLE IF NOT EXISTS diaporama (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    image_path VARCHAR(255) NOT NULL,
    text_content TEXT,
    text_position VARCHAR(50),
    text_color VARCHAR(20),
    text_size INT,
    background_color VARCHAR(20),
    display_order INT NOT NULL DEFAULT 0,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table bandeau_defilant
CREATE TABLE IF NOT EXISTS bandeau_defilant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    active BOOLEAN DEFAULT true,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 