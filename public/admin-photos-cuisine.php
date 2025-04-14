<?php
session_start();
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/functions.php';

// Vérification de l'authentification
if (!isAuthenticated()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administration - Photos Cuisine</title>
    <link rel="stylesheet" href="/styles/common.css">
    <style>
        /* Styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: #f0f2f5;
            padding: 2rem;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .admin-header h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .admin-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .photo-card {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .photo-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-delete {
            position: absolute;
            top: 50%;
            left: -40px;
            transform: translateY(-50%);
            background: rgba(255, 0, 0, 0.8);
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            color: white;
        }

        .upload-zone {
            border: 2px dashed #5eb3ec;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .upload-zone:hover {
            background-color: rgba(94, 179, 236, 0.1);
        }

        #fileInput {
            display: none;
        }

        .photo-card.selected {
            border: 3px solid #5eb3ec;
        }

        .selection-controls {
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .selection-count {
            background: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .publish-button {
            display: none;
        }

        .publish-button.visible {
            display: block;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background: #5eb3ec;
            color: white;
        }

        .btn-primary:hover {
            background: #4a90c9;
        }

        .status-message {
            display: none;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }

        .photo-controls {
            position: absolute;
            bottom: 5px;
            right: 5px;
            display: flex;
            gap: 5px;
        }

        .photo-rotate-left, .photo-rotate-right {
            background: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .photo-rotate-left:hover, .photo-rotate-right:hover {
            background: white;
        }
    </style>
</head>

<body>
    <button id="backButton" class="back-button">← Retour</button>
    <script src="/js/auth-check.js"></script>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Administration des Photos de Cuisine</h1>
            <p>Gérez ici les photos qui apparaissent dans la galerie de la cuisine</p>
        </header>

        <div class="upload-zone" id="uploadZone">
            <input type="file" id="fileInput" accept="image/*" multiple />
            <p>Cliquez ou glissez-déposez vos photos ici</p>
            <button class="btn btn-primary">Sélectionner des fichiers</button>
        </div>

        <div class="selection-controls">
            <div class="selection-count" id="selectionCount">0 photo sélectionnée</div>
            <button class="btn btn-primary publish-button" id="publishButton">
                Publier les photos sélectionnées
            </button>
        </div>

        <div class="status-message" id="statusMessage"></div>

        <div class="photos-grid" id="photosGrid">
            <!-- Les photos seront chargées ici dynamiquement -->
        </div>
    </div>

    <script>
        // Initialisation des variables
        const elements = {
            uploadZone: document.getElementById('uploadZone'),
            fileInput: document.getElementById('fileInput'),
            photosGrid: document.getElementById('photosGrid'),
            statusMessage: document.getElementById('statusMessage'),
            publishButton: document.getElementById('publishButton'),
            selectionCount: document.getElementById('selectionCount'),
            backButton: document.getElementById('backButton')
        };

        // Fonction optimisée pour charger les photos
        async function loadPhotos() {
            try {
                const response = await fetch('/api/cuisine-photos.php');
                const photos = await response.json();

                if (!photos.length) {
                    elements.photosGrid.innerHTML = '<p>Aucune photo disponible</p>';
                    return;
                }

                const photoCards = photos.map(photo => `
                    <div class="photo-card" data-photo-id="${photo.id}">
                        <img src="${photo.url}" alt="Photo cuisine" loading="lazy" data-id="${photo.id}" />
                        <div class="photo-controls">
                            <button class="photo-rotate-left" data-id="${photo.id}" title="Rotation gauche">↺</button>
                            <button class="photo-rotate-right" data-id="${photo.id}" title="Rotation droite">↻</button>
                            <button class="photo-delete" data-id="${photo.id}">×</button>
                        </div>
                    </div>
                `).join('');

                elements.photosGrid.innerHTML = photoCards;
            } catch (error) {
                console.error('Erreur de chargement:', error);
                showStatus('Erreur lors du chargement des photos.', false);
            }
        }

        // Fonction optimisée pour supprimer une photo
        async function deletePhoto(id) {
            if (!confirm('Voulez-vous vraiment supprimer cette photo ?')) {
                return;
            }

            try {
                const response = await fetch('/api/delete-photo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, type: 'cuisine' })
                });

                if (!response.ok) {
                    throw new Error('Erreur lors de la suppression');
                }

                await loadPhotos();
                showStatus('Photo supprimée avec succès !', true);

            } catch (error) {
                console.error('Erreur:', error);
                showStatus('Erreur lors de la suppression de la photo.', false);
            }
        }

        function showStatus(message, isSuccess) {
            elements.statusMessage.textContent = message;
            elements.statusMessage.className = `status-message ${isSuccess ? 'status-success' : 'status-error'}`;
            elements.statusMessage.style.display = 'block';
            setTimeout(() => elements.statusMessage.style.display = 'none', 3000);
        }

        // Initialisation avec délégation d'événements
        document.addEventListener('DOMContentLoaded', () => {
            loadPhotos();
            
            // Gestion des clics sur les boutons de suppression
            elements.photosGrid.addEventListener('click', async (e) => {
                if (e.target.matches('.photo-rotate-left')) {
                    const id = e.target.dataset.id;
                    await rotateImage(id, -90);
                } else if (e.target.matches('.photo-rotate-right')) {
                    const id = e.target.dataset.id;
                    await rotateImage(id, 90);
                } else if (e.target.matches('.photo-delete')) {
                    if (confirm('Voulez-vous vraiment supprimer cette photo ?')) {
                        const id = e.target.dataset.id;
                        await deletePhoto(id);
                    }
                }
            });

            elements.backButton?.addEventListener('click', () => window.history.back());
        });

        uploadZone.addEventListener('click', () => elements.fileInput.click());
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.style.borderColor = '#5eb3ec';
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            handleFiles(e.dataTransfer.files);
        });

        elements.fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        async function handleFiles(files) {
            for (const file of files) {
                if (!file.type.startsWith('image/')) {
                    showStatus('Seules les images sont acceptées.', false);
                    continue;
                }

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch('/api/upload_photo.php?type=cuisine', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        showStatus('Photo téléchargée avec succès !', true);
                        loadPhotos();
                    } else {
                        throw new Error('Erreur lors du téléchargement');
                    }
                } catch (error) {
                    showStatus('Erreur lors du téléchargement de la photo.', false);
                }
            }
        }

        // Fonction pour faire pivoter une image
        async function rotateImage(id, degrees) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('angle', degrees);

                // Trouver l'image à pivoter
                const card = document.querySelector(`[data-photo-id="${id}"]`);
                const img = card?.querySelector('img');
                if (card) {
                    card.style.opacity = '0.5';
                }

                const response = await fetch('/api/rotate-photo.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Erreur lors de la rotation');
                }

                // Forcer le rechargement de l'image
                if (img) {
                    const currentSrc = img.src.split('?')[0];
                    img.src = `${currentSrc}?t=${Date.now()}`;
                    card.style.opacity = '1';
                }

                showStatus('Photo pivotée avec succès !', true);

            } catch (error) {
                console.error('Erreur:', error);
                if (card) {
                    card.style.opacity = '1';
                }
                showStatus('Erreur lors de la rotation de la photo.', false);
            }
        }
    </script>
</body>

</html>