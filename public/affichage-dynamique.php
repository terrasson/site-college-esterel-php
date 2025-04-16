<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/functions.php';
require_once __DIR__ . '/api/database.php';

// Récupérer le type depuis l'URL (cuisine par défaut)
$type = $_GET['type'] ?? 'cuisine';

// Valider le type
if (!in_array($type, ['cuisine', 'direction'])) {
    $type = 'cuisine';
}

// Sélectionner la bonne table
$table = $type === 'direction' ? 'photos_direction' : 'photos_cuisine';

try {
    $pdo = getPDOConnection();
    
    // Utiliser une requête préparée
    $stmt = $pdo->prepare("SELECT url FROM " . $table . " ORDER BY id DESC");
    $stmt->execute();
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer la configuration du diaporama
    $configFile = __DIR__ . '/api/diaporama_config.json';
    $config = [];
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
    }

    // Sélectionner la configuration selon le type
    $currentConfig = $config[$type] ?? ['medias' => [], 'schedules' => []];

} catch (PDOException $e) {
    error_log("Erreur PDO : " . $e->getMessage());
    die("Une erreur est survenue lors de l'accès à la base de données");
} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    die("Une erreur est survenue");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Site du Collège de l'Estérel</title>
  <link rel="stylesheet" href="/css/responsive-page.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/2.0.10/css/weather-icons.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
  <style>
    /* ... autres styles existants ... */

    /* Styles pour le bandeau défilant */
    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
    }

    .news-ticker {
      width: 100%;
      height: 40px;
      overflow: hidden;
      background-color: #333;
    }

    .news-ticker-content {
      height: 100%;
      display: flex;
      align-items: center;
      white-space: nowrap;
    }

    #ticker-text {
      display: inline-block;
      padding-left: 100%;
      color: white;
      font-size: 1.2em;
      animation: ticker 20s linear infinite;
    }

    @keyframes ticker {
      0% {
        transform: translateX(100%);
      }
      100% {
        transform: translateX(-100%);
      }
    }

    /* Ajustement pour éviter que le contenu soit caché derrière le bandeau */
    .main-content {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 200px);
      width: 100%;
      padding: 20px;
      box-sizing: border-box;
    }

    .ticker {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: rgba(0, 0, 0, 0.8);
      color: white;
      padding: 10px 0;
      z-index: 1000;
    }

    .ticker-content {
      width: 100%;
      overflow: hidden;
      white-space: nowrap;
    }

    #ticker-text {
      display: inline-block;
      padding-left: 100%;
      animation: ticker 30s linear infinite;
    }

    @keyframes ticker {
      0% { transform: translateX(0); }
      100% { transform: translateX(-100%); }
    }

    .ticker-text {
      display: inline-block;
      padding: 0 20px;
      animation: ticker var(--duration) linear infinite;
    }

    /* Ajout d'un second élément pour une transition fluide */
    .ticker-content::after {
      content: attr(data-text);
      position: absolute;
      white-space: nowrap;
      padding-left: 100%;
      left: 100%;
      top: 0;
    }

    @keyframes scroll {
        from {
            transform: translate3d(0, 0, 0);
        }
        to {
            transform: translate3d(-100%, 0, 0);
        }
    }

    .scrolling-text {
        display: inline-block;
        white-space: nowrap;
        padding-left: 100%;
        animation: scroll 60s linear infinite;
    }

    .news-banner {
        background-color: #f0f0f0;
        overflow: hidden;
        padding: 10px 0;
        width: 100%;
    }

    .slideshow-container {
        display: flex;
        justify-content: center;
        gap: 30px;
        width: calc(100% - 40px);
        height: calc(100vh - 270px);
        margin: 20px 20px;
        box-sizing: border-box;
    }

    .slideshow-section {
        position: relative;
        width: calc((100vh - 270px) * 1.5);
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
    }

    .media-container {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    .slideshow-media {
        position: absolute;
        width: 100% !important;
        height: 100% !important;
        object-fit: contain;
        opacity: 0;
        transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        transform-origin: center center;
    }

    iframe.slideshow-media {
        width: 100% !important;
        height: 100% !important;
        border: none;
        background: white;
    }

    /* Styles pour les transitions */
    .slideshow-media.active {
        opacity: 1;
    }

    /* Ajustements responsifs */
    @media (min-width: 1400px) {
        .slideshow-container {
            width: 90%;
        }
    }

    @media (max-width: 1200px) {
        .slideshow-container {
            padding: 0 15px;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 10px;
        }

        .slideshow-container {
            flex-direction: column;
            height: auto;
            padding: 0 10px;
        }

        .slideshow-section {
            flex: none;
            width: 100%;
            height: 45vh;
            margin: 10px 0;
        }

        .banner-right {
            gap: 10px;
        }
        
        .logo img {
            max-height: 40px;
        }
    }

    /* Style spécifique pour le bouton invisible */
    .hidden-back-button {
        position: fixed;
        top: 0;
        left: 0;
        width: 50px;
        height: 50px;
        background: transparent;
        border: none;
        cursor: pointer;
        z-index: 1000;
    }

    /* Aucun effet visuel au survol ou au clic */
    .hidden-back-button:hover,
    .hidden-back-button:active,
    .hidden-back-button:focus {
        outline: none;
        background: transparent;
    }

    /* Transition: Fondu */
    .transition-fade {
        opacity: 0;
        transform: none;
    }
    .transition-fade.active {
        opacity: 1;
        transform: none;
    }

    /* Transition: Glissement */
    .transition-slide {
        opacity: 0;
        transform: translateX(-100%);
    }
    .transition-slide.active {
        opacity: 1;
        transform: translateX(0);
    }

    /* Transition: Zoom */
    .transition-zoom {
        opacity: 0;
        transform: scale(0.8);
    }
    .transition-zoom.active {
        opacity: 1;
        transform: scale(1);
    }

    /* Transition: Fondu + Zoom */
    .transition-fade-zoom {
        opacity: 0;
        transform: scale(1.2);
    }
    .transition-fade-zoom.active {
        opacity: 1;
        transform: scale(1);
    }

    /* Transition: Glissement + Fondu */
    .transition-slide-fade {
        opacity: 0;
        transform: translateX(-50px);
    }
    .transition-slide-fade.active {
        opacity: 1;
        transform: translateX(0);
    }

    /* Transition: Spirale */
    .transition-spiral {
        opacity: 0;
        transform: rotate(-360deg) scale(0);
    }
    .transition-spiral.active {
        opacity: 1;
        transform: rotate(0) scale(1);
    }

    /* Ajouter des styles pour le texte sur les images */
    .text-overlay {
        position: absolute;
        z-index: 10;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        text-align: center;
        word-wrap: break-word;
        max-width: 40%;
        line-height: 1.4;
    }

    .text-overlay.active {
        opacity: 1;
    }

    /* Styles pour les transitions du texte */
    .text-overlay.fade {
        opacity: 0;
    }

    .text-overlay.fade.active {
        opacity: 1;
    }

    .text-overlay.slide {
        transform: translateY(20px);
        opacity: 0;
    }

    .text-overlay.slide.active {
        transform: translateY(0);
        opacity: 1;
    }

    .text-overlay.zoom {
        transform: scale(0.8);
        opacity: 0;
    }

    .text-overlay.zoom.active {
        transform: scale(1);
        opacity: 1;
    }

    /* Ajustements pour la police Dancing Script */
    @font-face {
        font-family: 'Dancing Script';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/dancingscript/v24/If2cXTr6YS-zF4S-kcSWSVi_sxjsohD9F50Ruu7BMSo3Sup6hNX6plRP.woff2) format('woff2');
    }

    .banner-right {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 25px;
        padding-right: 25px;
    }

    .logo img {
        max-height: 75px;
        width: auto;
        object-fit: contain;
        transition: all 0.3s ease;
    }

    .info-link {
        color: rgba(128, 128, 128, 0.8);
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s ease;
        margin-left: 10px;
    }

    .info-link:hover {
        color: white;
        transform: scale(1.1);
    }

    /* Ajuster la position relative pour le conteneur */
    .banner-container {
        position: relative;
    }

    /* Ajustements responsifs */
    @media (max-width: 1400px) {
        .logo img {
            max-height: 70px;
        }
    }

    @media (max-width: 1200px) {
        .logo img {
            max-height: 65px;
        }
    }

    @media (max-width: 992px) {
        .logo img {
            max-height: 60px;
        }
        .banner-right {
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .logo img {
            max-height: 50px;
        }
        .banner-right {
            gap: 15px;
            padding-right: 15px;
        }
    }

    /* Pour les écrans en mode portrait */
    @media (orientation: portrait) {
        .logo img {
            max-height: 55px;
        }
    }

    /* Pour les très petits écrans */
    @media (max-width: 480px) {
        .logo img {
            max-height: 45px;
        }
        .banner-right {
            gap: 10px;
            padding-right: 10px;
        }
    }

    /* Ajout des styles pour le mode portrait */
    @media (orientation: portrait) {
        .slideshow-container {
            flex-direction: column;
            height: calc(100vh - 200px); /* Réduire la hauteur totale */
            gap: 15px; /* Réduire l'espace entre les sections */
            margin: 10px;
            width: calc(100% - 20px);
        }

        .slideshow-section {
            width: 100% !important; /* Forcer la largeur complète */
            height: calc((100vh - 250px) / 2) !important; /* Diviser l'espace en deux */
            padding: 10px;
            margin: 0;
        }

        .media-container {
            height: 100% !important;
        }

        /* Ajuster la bannière en mode portrait */
        .header-banner {
            padding: 5px 0;
        }

        .banner-left {
            padding: 5px;
        }

        .banner-title {
            font-size: 1.2em;
        }

        .banner-date, .banner-time {
            font-size: 0.9em;
        }

        /* Ajuster la météo */
        .weather-container {
            transform: scale(0.9);
        }

        /* Ajuster le bandeau défilant */
        .ticker {
            height: 30px;
            padding: 5px 0;
        }

        /* Optimiser l'espace pour les logos */
        .logo img {
            max-height: 40px;
        }

        .banner-right {
            gap: 10px;
            padding-right: 10px;
        }
    }

    /* Assurer que rien ne dépasse en mode portrait sur les petits écrans */
    @media (orientation: portrait) and (max-height: 800px) {
        .slideshow-container {
            height: calc(100vh - 180px);
        }

        .slideshow-section {
            height: calc((100vh - 220px) / 2) !important;
        }

        .header-banner {
            padding: 2px 0;
        }

        .logo img {
            max-height: 35px;
        }
    }

    /* Optimisation pour les très grands écrans en portrait */
    @media (orientation: portrait) and (min-height: 1920px) {
        .slideshow-container {
            height: calc(100vh - 300px);
        }

        .slideshow-section {
            height: calc((100vh - 350px) / 2) !important;
        }
    }

    /* S'assurer que le contenu reste visible sur les écrans étroits */
    @media (orientation: portrait) and (max-width: 500px) {
        .banner-title {
            font-size: 1em;
        }

        .weather-container {
            transform: scale(0.8);
        }

        .logo img {
            max-height: 30px;
        }
    }

    #diaporama-container {
        width: 100%;
        height: 100vh;
        position: relative;
        overflow: hidden;
        background: #000;
    }

    .slide-container {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .text-container {
        text-align: center;
        max-width: 80%;
        word-wrap: break-word;
    }

    /* Transitions */
    .transition-fade {
        opacity: 0;
        animation: fadeIn 1s forwards;
    }

    .transition-slide {
        transform: translateX(100%);
        animation: slideIn 1s forwards;
    }

    .transition-zoom {
        transform: scale(0.8);
        opacity: 0;
        animation: zoomIn 1s forwards;
    }

    .transition-fade-zoom {
        transform: scale(1.2);
        opacity: 0;
        animation: fadeZoomIn 1s forwards;
    }

    @keyframes fadeIn {
        to { opacity: 1; }
    }

    @keyframes slideIn {
        to { transform: translateX(0); }
    }

    @keyframes zoomIn {
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes fadeZoomIn {
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
  </style>
</head>

<body>
  <button class="hidden-back-button"></button>
  
  <header class="header-banner">
    <div class="banner-container">
      <div class="banner-left">
        <h1 class="banner-title">Collège de l'Estérel</h1>
        <div class="banner-date">
          <script>
            const options = {
              weekday: "long",
              year: "numeric",
              month: "long",
              day: "numeric",
            };
            document.write(new Date().toLocaleDateString("fr-FR", options));
          </script>
        </div>
        <div class="banner-time" id="current-time"></div>
      </div>

      <div class="banner-center">
        <div class="weather-container">
          <div class="weather-icon">
            <i class="wi"></i>
          </div>
          <div class="weather-info">
            <div class="weather-temp">
              <span class="temp-value"></span>
              <i class="wi wi-thermometer"></i>
            </div>
            <div class="weather-desc"></div>
            <div class="weather-details">
              <span class="humidity">
                <i class="wi wi-humidity"></i>
                <span class="humidity-value"></span>
              </span>
              <span class="wind">
                <i class="wi wi-strong-wind"></i>
                <span class="wind-value"></span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="banner-right">
        <a href="#" class="logo">
          <img src="/assets/img/logo-ministere-education.png" alt="Logo Education national" />
        </a>
        <a href="#" class="logo">
          <img src="/assets/img/logo-departement.png" alt="Logo Departement" />
        </a>
        <a href="/contact.php" class="info-link" title="Contact et informations">ⓘ</a>
      </div>
    </div>
  </header>

  <main class="main-content">
    <div class="slideshow-container">
        <!-- Diaporama Cuisine -->
        <div id="cuisine-section" class="slideshow-section">
            <div class="media-container">
                <img id="slideshow-image-cuisine" class="slideshow-media" style="display: none;" alt="Contenu cuisine">
                <iframe id="slideshow-document-cuisine" class="slideshow-media" style="display: none;"></iframe>
            </div>
            <div id="cuisine-text" class="text-overlay"></div>
        </div>

        <!-- Diaporama Direction -->
        <div id="direction-section" class="slideshow-section">
            <div class="media-container">
                <img id="slideshow-image-direction" class="slideshow-media" style="display: none;" alt="Contenu direction">
                <iframe id="slideshow-document-direction" class="slideshow-media" style="display: none;"></iframe>
            </div>
            <div id="direction-text" class="text-overlay"></div>
        </div>
    </div>
  </main>

  <div class="ticker">
    <div class="ticker-content">
        <div id="ticker-text"></div>
    </div>
  </div>

  <script>
    function updateTime() {
      const timeElement = document.getElementById("current-time");
      const now = new Date();
      timeElement.textContent = now.toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      });
    }

    setInterval(updateTime, 1000);
    updateTime();

    const weatherIconMap = {
      "01d": "wi-day-sunny",
      "01n": "wi-night-clear",
      "02d": "wi-day-cloudy",
      "02n": "wi-night-alt-cloudy",
      "03d": "wi-cloud",
      "03n": "wi-cloud",
      "04d": "wi-cloudy",
      "04n": "wi-cloudy",
      "09d": "wi-day-showers",
      "09n": "wi-night-alt-showers",
      "10d": "wi-day-rain",
      "10n": "wi-night-alt-rain",
      "11d": "wi-day-thunderstorm",
      "11n": "wi-night-alt-thunderstorm",
      "13d": "wi-day-snow",
      "13n": "wi-night-alt-snow",
      "50d": "wi-day-fog",
      "50n": "wi-night-fog",
    };

    async function getWeather() {
      const apiKey = "c58762bad7036389cd0ca8dfc58bda59";
      const city = "Saint-Raphaël,FR";

      try {
        const response = await fetch(
          `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric&lang=fr`
        );
        const data = await response.json();

        const iconElement = document.querySelector(".weather-icon i");
        const tempElement = document.querySelector(".temp-value");
        const descElement = document.querySelector(".weather-desc");
        const humidityElement = document.querySelector(".humidity-value");
        const windElement = document.querySelector(".wind-value");

        iconElement.className = `wi ${weatherIconMap[data.weather[0].icon] || "wi-na"
          }`;

        tempElement.textContent = `${Math.round(data.main.temp)}°C`;
        descElement.textContent = data.weather[0].description;
        humidityElement.textContent = `${data.main.humidity}%`;
        windElement.textContent = `${Math.round(data.wind.speed * 3.6)} km/h`;
      } catch (error) {
        console.error("Erreur lors de la récupération de la météo:", error);
      }
    }

    getWeather();
    setInterval(getWeather, 30 * 60 * 1000);

    // Remplacer les fonctions de gestion du bandeau défilant
    let lastTickerMessage = ''; // Variable pour stocker le dernier message

    async function initNewsTicker() {
        try {
            const response = await fetch('/api/ticker-message.php');
            const data = await response.json();
            
            if (data && data.message) {
                const tickerElement = document.getElementById('ticker-text');
                tickerElement.textContent = data.message;
                
                // Appliquer la vitesse depuis la base de données
                const duration = data.speed || 30;
                tickerElement.style.animation = `ticker ${duration}s linear infinite`;
            }
        } catch (error) {
            console.error('Erreur ticker:', error);
        }
    }

    // Initialiser et rafraîchir toutes les 5 secondes
    document.addEventListener('DOMContentLoaded', () => {
        initNewsTicker();
        setInterval(initNewsTicker, 5000);
    });

    // Initialisation des variables globales
    let currentSection = 'cuisine';
    let diaporamaData = null;
    let currentIndex = {
        cuisine: 0,
        direction: 0
    };

    async function loadDiaporamaConfig() {
        try {
            console.log('Chargement de la configuration...');
            const response = await fetch('/api/diaporama_config.php');
            if (!response.ok) {
                throw new Error('Erreur lors du chargement de la configuration du diaporama');
            }
            const data = await response.json();
            console.log('Configuration reçue:', data);
            
            // Vérifier si nous avons des médias pour les deux sections
            if (data) {
                if (data.cuisine && data.cuisine.medias) {
                    console.log(`Nombre de médias trouvés pour cuisine: ${data.cuisine.medias.length}`);
                }
                if (data.direction && data.direction.medias) {
                    console.log(`Nombre de médias trouvés pour direction: ${data.direction.medias.length}`);
                }
            } else {
                console.log('Aucun média trouvé dans la configuration');
            }
            
            diaporamaData = data;
            startDiaporama('cuisine');
            startDiaporama('direction');
        } catch (error) {
            console.error('Erreur détaillée:', error);
        }
    }

    function startDiaporama(section) {
        console.log(`Démarrage du diaporama ${section}...`);
        
        if (!diaporamaData || !diaporamaData[section] || !diaporamaData[section].medias || diaporamaData[section].medias.length === 0) {
            console.log(`Pas de médias à afficher pour ${section}`);
            return;
        }

        console.log(`Lancement du diaporama ${section} avec`, diaporamaData[section].medias.length, 'médias');
        showCurrentMedia(section);
    }

    function showCurrentMedia(section) {
        console.log(`Affichage du média courant pour ${section}...`);
        const medias = diaporamaData[section].medias;
        if (!medias || medias.length === 0) {
            console.log(`Aucun média à afficher pour ${section}`);
            return;
        }

        const media = medias[currentIndex[section]];
        console.log(`Média en cours d'affichage pour ${section}:`, media);

        // Utiliser les éléments existants
        const imgElement = document.getElementById(`slideshow-image-${section}`);
        const textElement = document.getElementById(`${section}-text`);
        
        if (!imgElement || !textElement) {
            console.error(`Éléments du diaporama non trouvés pour ${section} !`);
            return;
        }

        // Afficher l'image
        imgElement.src = media.path;
        imgElement.style.display = 'block';
        imgElement.className = `slideshow-media transition-${media.transition || 'fade'}`;

        // Afficher le texte si présent
        if (media.comment) {
            textElement.textContent = media.comment;
            textElement.className = `text-overlay ${media.transition || 'fade'}`;
            textElement.style.fontFamily = media.font || 'Arial';
            textElement.style.fontSize = `${media.fontSize || 24}px`;
            textElement.style.color = media.textColor || '#ffffff';
            
            // Positionnement du texte
            textElement.style.position = 'absolute';
            textElement.style.margin = '20px';
            textElement.style.maxWidth = '40%';
            
            // Définir la position
            const position = media.textPosition || 'bottom-center';
            setTextPosition(textElement, position);
            
            if (media.textBackground) {
                textElement.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                textElement.style.padding = '10px 20px';
                textElement.style.borderRadius = '5px';
            }
        } else {
            textElement.textContent = '';
            textElement.className = 'text-overlay';
        }

        // Ajouter la classe active après un court délai
        setTimeout(() => {
            imgElement.classList.add('active');
            if (media.comment) {
                textElement.classList.add('active');
            }
        }, 50);

        // Passer au média suivant après la durée spécifiée
        const duration = media.duration || 5;
        setTimeout(() => {
            currentIndex[section] = (currentIndex[section] + 1) % medias.length;
            showCurrentMedia(section);
        }, duration * 1000);
    }

    function setTextPosition(element, position) {
        element.style.top = 'auto';
        element.style.bottom = 'auto';
        element.style.left = 'auto';
        element.style.right = 'auto';
        element.style.transform = 'none';

        switch(position) {
            case 'top-left':
                element.style.top = '0';
                element.style.left = '0';
                break;
            case 'top-center':
                element.style.top = '0';
                element.style.left = '50%';
                element.style.transform = 'translateX(-50%)';
                break;
            case 'top-right':
                element.style.top = '0';
                element.style.right = '0';
                break;
            case 'center-left':
                element.style.top = '50%';
                element.style.left = '0';
                element.style.transform = 'translateY(-50%)';
                break;
            case 'center':
                element.style.top = '50%';
                element.style.left = '50%';
                element.style.transform = 'translate(-50%, -50%)';
                break;
            case 'center-right':
                element.style.top = '50%';
                element.style.right = '0';
                element.style.transform = 'translateY(-50%)';
                break;
            case 'bottom-left':
                element.style.bottom = '0';
                element.style.left = '0';
                break;
            case 'bottom-center':
                element.style.bottom = '0';
                element.style.left = '50%';
                element.style.transform = 'translateX(-50%)';
                break;
            case 'bottom-right':
                element.style.bottom = '0';
                element.style.right = '0';
                break;
        }
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', () => {
        loadDiaporamaConfig();
        setInterval(loadDiaporamaConfig, 30000);

        // Gestion du bouton retour
        const backButton = document.querySelector('.hidden-back-button');
        if (backButton) {
            backButton.addEventListener('click', () => {
                const lastPage = sessionStorage.getItem('lastPage') || '/';
                window.location.href = lastPage;
                sessionStorage.removeItem('lastPage');
            });
        }

        // Stocker la page actuelle pour le retour
        const currentPage = window.location.pathname;
        if (!sessionStorage.getItem('lastPage')) {
            sessionStorage.setItem('lastPage', document.referrer || '/');
        }

        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('lastPage', currentPage);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('.hidden-back-button').addEventListener('click', () => {
            window.history.back();
        });
    });

    function applyTransition(currentSlide, nextSlide, transition = 'fade') {
        // Définir les animations en fonction du type de transition
        switch (transition) {
            case 'slide':
                currentSlide.style.animation = 'slideOut 1s forwards';
                nextSlide.style.animation = 'slideIn 1s forwards';
                break;
            
            case 'zoom':
                currentSlide.style.animation = 'zoomOut 1s forwards';
                nextSlide.style.animation = 'zoomIn 1s forwards';
                break;
            
            case 'fade-zoom':
                currentSlide.style.animation = 'fadeZoomOut 1s forwards';
                nextSlide.style.animation = 'fadeZoomIn 1s forwards';
                break;
            
            case 'slide-fade':
                currentSlide.style.animation = 'slideFadeOut 1s forwards';
                nextSlide.style.animation = 'slideFadeIn 1s forwards';
                break;
            
            case 'spiral':
                currentSlide.style.animation = 'spiralOut 1s forwards';
                nextSlide.style.animation = 'spiralIn 1s forwards';
                break;
            
            case 'flip':
                currentSlide.style.animation = 'flipOut 1s forwards';
                nextSlide.style.animation = 'flipIn 1s forwards';
                break;
            
            case 'bounce':
                currentSlide.style.animation = 'bounceOut 1s forwards';
                nextSlide.style.animation = 'bounceIn 1s forwards';
                break;
            
            case 'rotate':
                currentSlide.style.animation = 'rotateOut 1s forwards';
                nextSlide.style.animation = 'rotateIn 1s forwards';
                break;
            
            case 'blur':
                currentSlide.style.animation = 'blurOut 1s forwards';
                nextSlide.style.animation = 'blurIn 1s forwards';
                break;
            
            case 'fade':
            default:
                currentSlide.style.animation = 'fadeOut 1s forwards';
                nextSlide.style.animation = 'fadeIn 1s forwards';
                break;
        }
    }

    // Ajouter les styles CSS pour toutes les animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); }
            to { transform: translateX(-100%); }
        }
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes zoomOut {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(0.5); opacity: 0; }
        }
        @keyframes zoomIn {
            from { transform: scale(1.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes fadeZoomOut {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(1.2); opacity: 0; }
        }
        @keyframes fadeZoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes slideFadeOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(-50%); opacity: 0; }
        }
        @keyframes slideFadeIn {
            from { transform: translateX(50%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes spiralOut {
            from { transform: rotate(0) scale(1); opacity: 1; }
            to { transform: rotate(360deg) scale(0); opacity: 0; }
        }
        @keyframes spiralIn {
            from { transform: rotate(-360deg) scale(0); opacity: 0; }
            to { transform: rotate(0) scale(1); opacity: 1; }
        }
        
        @keyframes flipOut {
            from { transform: perspective(400px) rotateY(0); }
            to { transform: perspective(400px) rotateY(90deg); }
        }
        @keyframes flipIn {
            from { transform: perspective(400px) rotateY(-90deg); }
            to { transform: perspective(400px) rotateY(0); }
        }
        
        @keyframes bounceOut {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.5; }
            100% { transform: scale(0); opacity: 0; }
        }
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes rotateOut {
            from { transform: rotate(0); opacity: 1; }
            to { transform: rotate(180deg); opacity: 0; }
        }
        @keyframes rotateIn {
            from { transform: rotate(-180deg); opacity: 0; }
            to { transform: rotate(0); opacity: 1; }
        }
        
        @keyframes blurOut {
            from { filter: blur(0); opacity: 1; }
            to { filter: blur(20px); opacity: 0; }
        }
        @keyframes blurIn {
            from { filter: blur(20px); opacity: 0; }
            to { filter: blur(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
  </script>
</body>

</html>