<?php
session_start();
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/functions.php';

// Vérification de l'authentification
if (!isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

// Récupérer toutes les photos de cuisine depuis la base de données
$pdo = getPDOConnection();
$stmt = $pdo->query("SELECT id, url FROM photos_cuisine ORDER BY id DESC");
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Diaporamas</title>
    <link rel="stylesheet" href="/styles/common.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --error-color: #e74c3c;
            --border-color: #ddd;
            --bg-color: #f5f6fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg-color);
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .section-tabs {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: var(--primary-color);
            color: white;
            transition: all 0.3s ease;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn.active {
            background: var(--secondary-color);
        }

        .media-windows {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .media-window {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
        }

        .media-window h3 {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
            background: var(--bg-color);
            border-radius: 8px;
            max-height: 500px;
            overflow-y: auto;
        }

        .media-item {
            background: white;
            border-radius: 8px;
            padding: 0.8rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: move;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .preview-container {
            position: relative;
            width: 100%;
            padding-bottom: 75%; /* Ratio 4:3 */
            overflow: hidden;
            border-radius: 4px;
        }

        .preview-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-container.document {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-container.document::before {
            content: '📄';
            font-size: 2.5rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .preview-container.pdf::before {
            content: '📕';
        }

        .preview-container.doc::before,
        .preview-container.docx::before {
            content: '📘';
        }

        .preview-container.ppt::before,
        .preview-container.pptx::before {
            content: '📙';
        }

        .media-name {
            font-size: 0.9rem;
            color: #333;
            text-align: center;
            margin-top: 0.5rem;
            word-break: break-word;
            line-height: 1.2;
        }

        .media-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #666;
        }

        .media-type {
            padding: 0.2rem 0.5rem;
            background: var(--primary-color);
            color: white;
            border-radius: 3px;
            font-size: 0.7rem;
            align-self: flex-start;
        }

        /* Style pour la barre de défilement */
        .media-grid::-webkit-scrollbar {
            width: 8px;
        }

        .media-grid::-webkit-scrollbar-track {
            background: var(--bg-color);
            border-radius: 4px;
        }

        .media-grid::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .media-grid::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Style pour le drag and drop */
        .media-item.dragging {
            opacity: 0.5;
            transform: scale(0.95);
        }

        .timeline-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .timeline-item img {
            max-width: 100px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .timeline {
            border: 2px dashed var(--border-color);
            min-height: 200px;
            padding: 1rem;
            margin-top: 2rem;
            border-radius: 8px;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            margin-bottom: 0.5rem;
            border-radius: 4px;
            background: white;
        }

        .controls-container {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .save-btn {
            background: var(--secondary-color);
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 1rem 2rem;
            border-radius: 4px;
            color: white;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background: var(--secondary-color);
        }

        .notification.error {
            background: var(--error-color);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(0);
            }
        }

        .timeline-item {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: move;
        }

        .timeline-item.dragging {
            opacity: 0.5;
        }

        .timeline-item-content {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 10px;
            margin: 5px 0;
        }

        .timeline-item-content .preview-container {
            width: 100px;
            height: 75px;
            padding-bottom: 0;
            flex-shrink: 0;
        }

        .timeline-item-content .media-name {
            width: 150px;
            font-size: 0.85rem;
            margin: 0;
        }

        .timeline-controls {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: auto;
        }

        .comment-controls {
            width: 100%;
            padding: 5px;
            margin-top: 0;
            background: #f0f0f0;
            border-radius: 4px;
        }

        .text-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 5px;
        }

        .preview-container.document {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            border: 1px solid #ddd;
        }

        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
            font-size: 20px;
            font-weight: bold;
            line-height: 1;
        }

        .remove-btn:hover {
            background: #c0392b;
        }

        .duration-input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .transition-select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 120px;
        }

        .font-select, .position-select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .color-picker {
            width: 40px;
            height: 30px;
            padding: 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .font-size-input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .preview-text {
            position: absolute;
            padding: 5px;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
            word-wrap: break-word;
            text-align: center;
            left: 0;
            right: 0;
            transform: none;
        }

        .preview-text.top-center {
            top: 5px;
        }

        .preview-text.center {
            top: 50%;
            transform: translateY(-50%);
        }

        .preview-text.bottom-center {
            bottom: 5px;
        }

        .preview-text.with-background {
            background: rgba(0, 0, 0, 0.5);
            padding: 5px 10px;
        }

        .emoji-picker {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
        }

        .emoji-picker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            background: #f8f9fa;
            position: sticky;
            top: 0;
        }

        .emoji-picker-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            padding: 0 5px;
        }

        .emoji-picker-close:hover {
            color: #333;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 5px;
            padding: 10px;
        }

        .emoji-item {
            cursor: pointer;
            padding: 5px;
            text-align: center;
            transition: transform 0.1s, background-color 0.1s;
            border-radius: 4px;
        }

        .emoji-item:hover {
            transform: scale(1.2);
            background-color: #f0f0f0;
        }

        .clear-btn {
            background: var(--error-color);
            margin-left: 1rem;
        }
    </style>
</head>

<body>
    <button id="backButton" class="back-button">← Retour</button>
    <div class="container">
        <h1>Gestion des Diaporamas</h1>

        <div class="section-tabs">
            <button class="btn" onclick="switchSection('cuisine')" id="btn-cuisine">Diaporama Cuisine</button>
            <button class="btn" onclick="switchSection('direction')" id="btn-direction">Diaporama Direction</button>
        </div>

        <div class="media-windows">
            <!-- Photos -->
            <div class="media-window">
                <h3>Photos</h3>
                <div class="media-grid" id="available-images">
                    <?php foreach ($photos as $photo): ?>
                    <div class="media-item" draggable="true" data-type="image" data-path="<?= htmlspecialchars($photo['url']) ?>">
                        <div class="preview-container">
                            <img src="<?= htmlspecialchars($photo['url']) ?>" alt="<?= htmlspecialchars($photo['filename']) ?>">
                        </div>
                        <div class="media-name"><?= htmlspecialchars($photo['filename']) ?></div>
                        <div class="media-type">Image</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Documents -->
            <div class="media-window">
                <h3>Documents</h3>
                <div class="media-grid" id="available-documents">
                    <!-- Les documents seront injectés ici par JavaScript -->
                </div>
            </div>
        </div>

        <div class="timeline-container">
            <h2>Timeline</h2>
            <div class="timeline" id="timeline"></div>
        </div>

        <div class="controls-container">
            <button class="btn save-btn" onclick="saveTimeline()">Enregistrer le diaporama</button>
            <button class="btn clear-btn" onclick="clearTimeline()">Vider le diaporama</button>
        </div>
    </div>

    <script>
        let currentSection = 'cuisine';
        let timelineData = {
            cuisine: { medias: [], schedules: [] },
            direction: { medias: [], schedules: [] }
        };

        // Charger la configuration initiale
        async function loadConfig() {
            try {
                const response = await fetch('/api/diaporama_config.php');
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement de la configuration');
                }
                timelineData = await response.json();
                validateTimelineData();
                renderTimeline();
            } catch (error) {
                console.error('Erreur:', error);
                timelineData = {
                    cuisine: { medias: [], schedules: [] },
                    direction: { medias: [], schedules: [] }
                };
                renderTimeline();
            }
        }

        function handleDragStart(e) {
            const mediaData = {
                type: this.dataset.type,
                path: this.dataset.path,
                name: this.querySelector('.media-name').textContent
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(mediaData));
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        }

        function handleDrop(e) {
            e.preventDefault();
            try {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                if (!data.type || !data.path) {
                    throw new Error('Données de média invalides');
                }
                const mediaElement = createTimelineElement(data);
                updateTimelineData();
            } catch (error) {
                console.error('Erreur lors du drop:', error);
                showStatus('Erreur lors de l\'ajout du média', 'error');
            }
        }

        function createTimelineElement(media) {
            const timelineItem = document.createElement('div');
            timelineItem.className = 'timeline-item';
            timelineItem.draggable = true;

            const content = document.createElement('div');
            content.className = 'timeline-item-content';
            
            const previewContainer = document.createElement('div');
            previewContainer.className = 'preview-container';
            
            if (media.type === 'image') {
                const img = document.createElement('img');
                img.src = media.path;
                img.alt = media.name;
                previewContainer.appendChild(img);
            }

            content.innerHTML = `
                <div class="media-name">${media.name}</div>
                <div class="timeline-controls">
                    <input type="number" class="duration-input" value="5" min="1" max="60">
                    <select class="transition-select">
                        <option value="fade">Fondu</option>
                        <option value="slide">Glissement</option>
                        <option value="zoom">Zoom</option>
                        <option value="fade-zoom">Fondu + Zoom</option>
                    </select>
                    <button class="remove-btn" onclick="this.closest('.timeline-item').remove(); updateTimelineData();">×</button>
                </div>
                <div class="comment-controls">
                    <textarea class="comment-input" placeholder="Ajouter un commentaire"></textarea>
                    <div class="text-controls">
                        <select class="font-select">
                            <option value="Arial">Arial</option>
                            <option value="Dancing Script">Dancing Script</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Verdana">Verdana</option>
                        </select>
                        <input type="number" class="font-size-input" value="24" min="12" max="72">
                        <input type="color" class="color-picker" value="#ffffff">
                        <select class="position-select">
                            <option value="top-left" ${media.textPosition === 'top-left' ? 'selected' : ''}>Haut Gauche</option>
                            <option value="top-center" ${media.textPosition === 'top-center' ? 'selected' : ''}>Haut Centre</option>
                            <option value="top-right" ${media.textPosition === 'top-right' ? 'selected' : ''}>Haut Droite</option>
                            <option value="center-left" ${media.textPosition === 'center-left' ? 'selected' : ''}>Centre Gauche</option>
                            <option value="center" ${media.textPosition === 'center' ? 'selected' : ''}>Centre</option>
                            <option value="center-right" ${media.textPosition === 'center-right' ? 'selected' : ''}>Centre Droite</option>
                            <option value="bottom-left" ${media.textPosition === 'bottom-left' ? 'selected' : ''}>Bas Gauche</option>
                            <option value="bottom-center" ${media.textPosition === 'bottom-center' ? 'selected' : ''}>Bas Centre</option>
                            <option value="bottom-right" ${media.textPosition === 'bottom-right' ? 'selected' : ''}>Bas Droite</option>
                        </select>
                        <label><input type="checkbox" class="background-toggle">Fond</label>
                        <button class="emoji-btn" onclick="toggleEmojiPicker(this)">😀</button>
                    </div>
                </div>
            `;

            content.insertBefore(previewContainer, content.firstChild);
            timelineItem.appendChild(content);
            document.getElementById('timeline').appendChild(timelineItem);

            return timelineItem;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            loadConfig();
            
            const mediaItems = document.querySelectorAll('.media-item');
            const timeline = document.getElementById('timeline');

            mediaItems.forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
            });

            timeline.addEventListener('dragover', handleDragOver);
            timeline.addEventListener('drop', handleDrop);
        });

        function updateTimelineData() {
            const timeline = document.getElementById('timeline');
            const items = [...timeline.querySelectorAll('.timeline-item')].map(item => {
                const content = item.querySelector('.timeline-item-content');
                const img = content.querySelector('img');
                const durationInput = content.querySelector('.duration-input');
                const transitionSelect = content.querySelector('.transition-select');
                const commentInput = content.querySelector('.comment-input');
                const fontSelect = content.querySelector('.font-select');
                const fontSizeInput = content.querySelector('.font-size-input');
                const colorPicker = content.querySelector('.color-picker');
                const positionSelect = content.querySelector('.position-select');
                const backgroundToggle = content.querySelector('.background-toggle');

                return {
                    type: 'image',
                    path: img.src,
                    name: content.querySelector('.media-name').textContent,
                    duration: parseInt(durationInput.value) || 5,
                    transition: transitionSelect.value,
                    comment: commentInput.value,
                    font: fontSelect.value,
                    fontSize: parseInt(fontSizeInput.value),
                    textColor: colorPicker.value,
                    textPosition: positionSelect.value,
                    textBackground: backgroundToggle.checked
                };
            });

            timelineData[currentSection].medias = items;
        }

        async function saveTimeline() {
            try {
                updateTimelineData();
                const response = await fetch('/api/diaporama_config.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(timelineData)
                });

                if (!response.ok) {
                    throw new Error('Erreur lors de la sauvegarde');
                }

                showStatus('Diaporama sauvegardé avec succès', 'success');
            } catch (error) {
                console.error('Erreur:', error);
                showStatus('Erreur lors de la sauvegarde: ' + error.message, 'error');
            }
        }

        function showStatus(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        function validateTimelineData() {
            if (!timelineData.cuisine || !timelineData.direction) {
                timelineData = {
                    cuisine: { medias: [], schedules: [] },
                    direction: { medias: [], schedules: [] }
                };
            }
        }

        function clearTimeline() {
            if (confirm('Êtes-vous sûr de vouloir vider le diaporama ?')) {
                document.getElementById('timeline').innerHTML = '';
                updateTimelineData();
                showStatus('Diaporama vidé avec succès', 'success');
            }
        }

        // Mettre à jour la fonction switchSection pour recharger les médias
        function switchSection(section) {
            console.log('Changement de section vers:', section);
            currentSection = section;

            // Mettre à jour l'apparence des boutons
            document.getElementById('btn-cuisine').classList.toggle('active', section === 'cuisine');
            document.getElementById('btn-direction').classList.toggle('active', section === 'direction');

            // Charger les médias de la nouvelle section
            loadAvailableMedia(section);
            // Mettre à jour l'affichage de la timeline
            updateTimelineDisplay();
        }

        // Fonction pour charger les médias disponibles
        async function loadAvailableMedia(section) {
            try {
                const response = await fetch(`/api/list-photos.php?section=${section}`);
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des médias');
                }
                const photos = await response.json();
                
                // Mettre à jour la grille des images
                const imageGrid = document.getElementById('available-images');
                imageGrid.innerHTML = photos.map(photo => `
                    <div class="media-item" draggable="true" data-type="image" data-path="${photo.url}">
                        <div class="preview-container">
                            <img src="${photo.url}" alt="Photo">
                        </div>
                        <div class="media-name">Photo</div>
                        <div class="media-type">Image</div>
                    </div>
                `).join('');

                // Réinitialiser le drag and drop
                initializeDragAndDrop();
            } catch (error) {
                console.error('Erreur:', error);
                showStatus('Erreur lors du chargement des médias', 'error');
            }
        }

        // Fonction pour initialiser le drag and drop
        function initializeDragAndDrop() {
            const mediaItems = document.querySelectorAll('.media-item');
            const timeline = document.getElementById('timeline');

            mediaItems.forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
            });

            timeline.addEventListener('dragover', handleDragOver);
            timeline.addEventListener('drop', handleDrop);
        }

        // Fonction pour mettre à jour l'affichage de la timeline
        function updateTimelineDisplay() {
            const timeline = document.getElementById('timeline');
            if (!timeline) {
                console.error('Timeline element not found');
                return;
            }

            timeline.innerHTML = ''; // Vider la timeline actuelle

            const currentData = timelineData[currentSection];
            console.log('Mise à jour de l\'affichage pour la section:', currentSection, currentData);

            if (currentData && currentData.medias && Array.isArray(currentData.medias)) {
                currentData.medias.forEach(media => {
                    if (media) {
                        addMediaToTimeline(media);
                    }
                });
            }
        }

        // Fonction pour ajouter un média à la timeline
        function addMediaToTimeline(media) {
            console.log('Ajout du média à la timeline:', media);
            const timeline = document.getElementById('timeline');
            const element = createTimelineElement(media);

            // Ajouter les écouteurs d'événements pour les modifications de texte
            const commentInput = element.querySelector('.comment-input');
            const textControls = element.querySelectorAll('select, input[type="number"], input[type="color"], input[type="checkbox"]');

            // Mettre à jour la prévisualisation et les données lors de la modification du texte
            commentInput.addEventListener('input', function() {
                updatePreview(this);
                updateTimelineData();
            });

            // Mettre à jour pour tous les contrôles de texte
            textControls.forEach(control => {
                control.addEventListener('change', function() {
                    updatePreview(this);
                    updateTimelineData();
                });
            });

            timeline.appendChild(element);
        }

        // Ajout des nouvelles fonctions pour gérer les emojis
        function toggleEmojiPicker(button) {
            const existingPicker = document.querySelector('.emoji-picker');
            if (existingPicker) {
                existingPicker.remove();
                return;
            }

            const picker = document.createElement('div');
            picker.className = 'emoji-picker';
            
            // Liste étendue d'emojis avec plus de visages et d'expressions
            const emojis = [
                // Visages et expressions
                '😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', 
                '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🥸', '🤩',
                '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢',
                '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🫣',
                // Gestes et mains
                '👍', '👎', '👌', '🤌', '🤏', '✌️', '🤞', '🫰', '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇',
                // Symboles et décorations
                '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗',
                '⭐', '✨', '💫', '🌟', '✅', '❌', '💯', '🎯', '🎨', '🎭', '🎪',
                // Éducation et école
                '📚', '📖', '📝', '✏️', '📏', '📐', '🎒', '🏫', '🎓', '👨‍🏫', '👩‍🏫'
            ];
            
            // Ajouter un bouton de fermeture
            picker.innerHTML = `
                <div class="emoji-picker-header">
                    <span>Choisir un emoji</span>
                    <button class="emoji-picker-close" onclick="closeEmojiPicker()">×</button>
                </div>
                <div class="emoji-grid">
                    ${emojis.map(emoji => 
                        `<span class="emoji-item" onclick="addEmoji('${emoji}', this)">${emoji}</span>`
                    ).join('')}
                </div>
            `;

            const controls = button.closest('.text-controls');
            controls.appendChild(picker);
        }

        // Ajouter cette nouvelle fonction pour fermer le picker
        function closeEmojiPicker() {
            const picker = document.querySelector('.emoji-picker');
            if (picker) {
                picker.remove();
            }
        }

        // Modifier la fonction addEmoji pour mettre à jour les données
        function addEmoji(emoji, element) {
            const timelineItem = element.closest('.timeline-item-content');
            const commentInput = timelineItem.querySelector('.comment-input');
            
            // Ajouter l'emoji au texte
            commentInput.value += emoji;
            
            // Fermer le sélecteur d'emoji
            closeEmojiPicker();
            
            // Mettre à jour la prévisualisation
            updatePreview(commentInput);
            
            // Mettre à jour les données
            updateTimelineData();
        }

        // Fonction pour mettre à jour la prévisualisation
        function updatePreview(element) {
            const timelineItem = element.closest('.timeline-item-content');
            const previewText = timelineItem.querySelector('.preview-text');
            
            // Si le previewText n'existe pas, le créer
            if (!previewText) {
                const newPreviewText = document.createElement('div');
                newPreviewText.className = 'preview-text';
                const previewContainer = timelineItem.querySelector('.preview-container');
                previewContainer.appendChild(newPreviewText);
            }

            const commentInput = timelineItem.querySelector('.comment-input');
            const fontSelect = timelineItem.querySelector('.font-select');
            const fontSizeInput = timelineItem.querySelector('.font-size-input');
            const colorPicker = timelineItem.querySelector('.color-picker');
            const positionSelect = timelineItem.querySelector('.position-select');
            const backgroundToggle = timelineItem.querySelector('.background-toggle');

            const text = commentInput.value.trim();
            
            if (text) {
                // Réinitialiser toutes les classes
                previewText.className = 'preview-text';
                
                // Ajouter la position
                previewText.classList.add(positionSelect.value);
                
                // Ajouter le fond si nécessaire
                if (backgroundToggle && backgroundToggle.checked) {
                    previewText.classList.add('with-background');
                }

                // Mettre à jour les styles
                previewText.textContent = text;
                previewText.style.fontFamily = fontSelect.value === 'Dancing Script' ? "'Dancing Script', cursive" : fontSelect.value;
                previewText.style.fontSize = `${fontSizeInput.value}px`;
                previewText.style.color = colorPicker.value;
                previewText.style.display = 'block';
            } else {
                // Masquer le texte s'il est vide
                previewText.style.display = 'none';
            }

            // Mettre à jour les données
            updateTimelineData();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('backButton').addEventListener('click', () => {
                window.history.back();
            });
        });

        async function addToSlider(id) {
            try {
                const response = await fetch('/api/add-to-slider.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                if (!response.ok) {
                    throw new Error('Erreur lors de l\'ajout au diaporama');
                }

                const data = await response.json();
                showStatus('Photo ajoutée au diaporama !', true);

            } catch (error) {
                console.error('Erreur:', error);
                showStatus('Erreur lors de l\'ajout au diaporama.', false);
            }
        }

        // Gestionnaire d'événements pour les boutons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('photo-add-to-slider')) {
                const id = e.target.dataset.id;
                addToSlider(id);
            }
        });

        function renderTimeline() {
            const timeline = document.getElementById('timeline');
            timeline.innerHTML = ''; // Vider la timeline actuelle
            
            const currentMedias = timelineData[currentSection].medias;
            if (!currentMedias || !Array.isArray(currentMedias)) return;

            currentMedias.forEach(media => {
                if (!media || !media.path) return;
                
                const timelineItem = document.createElement('div');
                timelineItem.className = 'timeline-item';
                timelineItem.draggable = true;

                const content = document.createElement('div');
                content.className = 'timeline-item-content';
                
                const previewContainer = document.createElement('div');
                previewContainer.className = 'preview-container';
                
                if (media.type === 'image') {
                    const img = document.createElement('img');
                    img.src = media.path;
                    img.alt = media.name || '';
                    previewContainer.appendChild(img);
                }

                content.innerHTML = `
                    <div class="media-name">${media.name || ''}</div>
                    <div class="timeline-controls">
                        <input type="number" class="duration-input" value="${media.duration || 5}" min="1" max="60">
                        <select class="transition-select">
                            <option value="fade" ${media.transition === 'fade' ? 'selected' : ''}>Fondu</option>
                            <option value="slide" ${media.transition === 'slide' ? 'selected' : ''}>Glissement</option>
                            <option value="zoom" ${media.transition === 'zoom' ? 'selected' : ''}>Zoom</option>
                            <option value="fade-zoom" ${media.transition === 'fade-zoom' ? 'selected' : ''}>Fondu + Zoom</option>
                        </select>
                        <button class="remove-btn" onclick="this.closest('.timeline-item').remove(); updateTimelineData();">×</button>
                    </div>
                    <div class="comment-controls">
                        <textarea class="comment-input" placeholder="Ajouter un commentaire">${media.comment || ''}</textarea>
                        <div class="text-controls">
                            <select class="font-select">
                                <option value="Arial" ${media.font === 'Arial' ? 'selected' : ''}>Arial</option>
                                <option value="Dancing Script" ${media.font === 'Dancing Script' ? 'selected' : ''}>Dancing Script</option>
                                <option value="Times New Roman" ${media.font === 'Times New Roman' ? 'selected' : ''}>Times New Roman</option>
                                <option value="Verdana" ${media.font === 'Verdana' ? 'selected' : ''}>Verdana</option>
                            </select>
                            <input type="number" class="font-size-input" value="${media.fontSize || 24}" min="12" max="72">
                            <input type="color" class="color-picker" value="${media.textColor || '#ffffff'}">
                            <select class="position-select">
                                <option value="top-left" ${media.textPosition === 'top-left' ? 'selected' : ''}>Haut Gauche</option>
                                <option value="top-center" ${media.textPosition === 'top-center' ? 'selected' : ''}>Haut Centre</option>
                                <option value="top-right" ${media.textPosition === 'top-right' ? 'selected' : ''}>Haut Droite</option>
                                <option value="center-left" ${media.textPosition === 'center-left' ? 'selected' : ''}>Centre Gauche</option>
                                <option value="center" ${media.textPosition === 'center' ? 'selected' : ''}>Centre</option>
                                <option value="center-right" ${media.textPosition === 'center-right' ? 'selected' : ''}>Centre Droite</option>
                                <option value="bottom-left" ${media.textPosition === 'bottom-left' ? 'selected' : ''}>Bas Gauche</option>
                                <option value="bottom-center" ${media.textPosition === 'bottom-center' ? 'selected' : ''}>Bas Centre</option>
                                <option value="bottom-right" ${media.textPosition === 'bottom-right' ? 'selected' : ''}>Bas Droite</option>
                            </select>
                            <label><input type="checkbox" class="background-toggle" ${media.textBackground ? 'checked' : ''}>Fond</label>
                            <button class="emoji-btn" onclick="toggleEmojiPicker(this)">😀</button>
                        </div>
                    </div>
                `;

                content.insertBefore(previewContainer, content.firstChild);
                timelineItem.appendChild(content);
                timeline.appendChild(timelineItem);
            });
        }
    </script>
</body>

</html>