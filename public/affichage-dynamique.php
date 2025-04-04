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
      overflow: hidden;
    }

    .ticker-content {
      position: relative;
      width: 100%;
      overflow: hidden;
    }

    #ticker-text {
      display: inline-block;
      white-space: nowrap;
      padding-left: 100%;
      transform: translateX(0);
    }

    @keyframes ticker {
      from {
        transform: translateX(100%);
      }
      to {
        transform: translateX(-100%);
      }
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
        transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
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
    <div id="ticker-text"></div>
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
            console.log('Tentative de récupération du message...');
            const response = await fetch('/api/ticker-message');
            if (!response.ok) {
                console.log('Réponse non OK:', response.status);
                return;
            }

            const data = await response.json();
            console.log('Message reçu:', data);
            
            const tickerElement = document.getElementById('ticker-text');
            if (tickerElement && data.message) {
                tickerElement.textContent = data.message;
                const duration = data.speed || 30;
                tickerElement.style.animation = `ticker ${duration}s linear infinite`;
            }
        } catch (error) {
            console.log('Erreur détaillée:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initNewsTicker();
        setInterval(initNewsTicker, 5000);
    });

    // Initialisation des variables globales
    let cuisineConfig = { medias: [] };
    let directionConfig = { medias: [] };
    let cuisineIndex = 0;
    let directionIndex = 0;
    let cuisineTimeout = null;
    let directionTimeout = null;
    let isFirstLoad = true;

    // Fonction de chargement des configurations
    async function loadConfigurations() {
        try {
            // Forcer le chargement au premier appel
            if (!isFirstLoad) {
                const cacheKey = 'diaporama_config_timestamp';
                const lastCheck = sessionStorage.getItem(cacheKey);
                const now = Date.now();

                // Ne vérifier que toutes les 30 secondes sauf au premier chargement
                if (lastCheck && (now - parseInt(lastCheck)) < 30000) {
                    return;
                }
            }

            console.log('Chargement des configurations...');
            const response = await fetch('/api/diaporama-config');
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            const config = await response.json();

            // Mettre à jour le timestamp
            sessionStorage.setItem('diaporama_config_timestamp', Date.now().toString());

            // Toujours charger au premier démarrage
            const configChanged = isFirstLoad || JSON.stringify(config) !== JSON.stringify({
                cuisine: cuisineConfig,
                direction: directionConfig
            });

            if (configChanged) {
                console.log('Nouvelle configuration détectée ou premier chargement');
                
                if (cuisineTimeout) clearTimeout(cuisineTimeout);
                if (directionTimeout) clearTimeout(directionTimeout);

                // Réinitialiser les indices si nécessaire
                if (config.cuisine?.medias?.length > 0) {
                    cuisineConfig = config.cuisine;
                    cuisineIndex = 0;
                    showMedia('cuisine', cuisineConfig.medias[cuisineIndex]);
                } else {
                    console.log('Aucun média trouvé pour la cuisine');
                }

                if (config.direction?.medias?.length > 0) {
                    directionConfig = config.direction;
                    directionIndex = 0;
                    showMedia('direction', directionConfig.medias[directionIndex]);
                } else {
                    console.log('Aucun média trouvé pour la direction');
                }

                isFirstLoad = false;
            }
        } catch (error) {
            console.error('Erreur lors du chargement des configurations:', error);
            // Réessayer dans 5 secondes en cas d'erreur
            setTimeout(loadConfigurations, 5000);
        }
    }

    function showMedia(section, media) {
        if (!media || !media.path) {
            console.error(`Média invalide pour ${section}:`, media);
            return;
        }

        console.log(`Affichage du média pour ${section}:`, media);

        const imgElement = document.getElementById(`slideshow-image-${section}`);
        const iframeElement = document.getElementById(`slideshow-document-${section}`);
        const textOverlay = document.getElementById(`${section}-text`);
        const mediaContainer = imgElement.parentElement;

        if (!imgElement || !iframeElement || !textOverlay || !mediaContainer) {
            console.error(`Éléments DOM manquants pour ${section}`);
            return;
        }

        // Nettoyer les transitions précédentes
        const elements = [imgElement, iframeElement];
        elements.forEach(el => {
            el.classList.remove('active');
            el.style.display = 'none';
            el.className = 'slideshow-media';
        });

        textOverlay.classList.remove('active');
        textOverlay.style.display = 'none';
        textOverlay.className = 'text-overlay';

        const transition = media.transition || 'fade';
        const duration = Math.max(5, media.duration || 5) * 1000;

        const showWithTransition = (element) => {
            // Configurer le texte avant d'afficher le média
            if (media.comment && media.comment.trim() !== '') {
                configureTextOverlay(textOverlay, media);
                textOverlay.style.display = 'block';
                textOverlay.classList.add(`transition-${transition}`);
            }

            // Afficher et configurer le média
            element.style.display = 'block';
            element.classList.add(`transition-${transition}`);
            
            // Forcer un reflow avant d'ajouter les classes active
            void element.offsetWidth;
            void textOverlay.offsetWidth;

            // Activer les transitions simultanément
            requestAnimationFrame(() => {
                element.classList.add('active');
                if (media.comment && media.comment.trim() !== '') {
                    textOverlay.classList.add('active');
                }
            });
        };

        if (media.type === 'image') {
            const tempImg = new Image();
            tempImg.onload = () => {
                imgElement.src = media.path;
                showWithTransition(imgElement);
            };
            tempImg.onerror = () => {
                console.error(`Erreur de chargement de l'image pour ${section}:`, media.path);
                scheduleNextMedia(section);
            };
            tempImg.src = media.path;
        } else if (media.type === 'document') {
            if (media.path.toLowerCase().endsWith('.pdf')) {
                const viewerUrl = `/pdfjs/web/viewer.html?file=${encodeURIComponent(media.path)}`;
                iframeElement.src = viewerUrl;
                showWithTransition(iframeElement);

                iframeElement.onerror = () => {
                    console.error(`Erreur de chargement du document pour ${section}:`, media.path);
                    scheduleNextMedia(section);
                };
            } else {
                console.error(`Type de document non supporté pour ${section}:`, media.path);
                scheduleNextMedia(section);
                return;
            }
        }

        scheduleNextMedia(section, duration);
    }

    function configureTextOverlay(textOverlay, media) {
        // Réinitialiser les styles
        textOverlay.style = '';
        textOverlay.className = 'text-overlay';

        // Appliquer le texte et les styles
        textOverlay.textContent = media.comment;

        // Appliquer la police avec gestion spéciale pour Dancing Script
        if (media.fontFamily === 'Dancing Script') {
            textOverlay.style.fontFamily = "'Dancing Script', cursive";
        } else {
            textOverlay.style.fontFamily = media.fontFamily || 'Arial';
        }

        // Appliquer les autres styles
        textOverlay.style.fontSize = `${media.fontSize || 24}px`;
        textOverlay.style.color = media.textColor || '#ffffff';
        textOverlay.style.fontWeight = media.fontWeight || 'normal';
        textOverlay.style.fontStyle = media.fontStyle || 'normal';
        textOverlay.style.padding = '10px';
        textOverlay.style.borderRadius = '4px';
        textOverlay.style.maxWidth = '80%';
        textOverlay.style.textAlign = 'center';
        textOverlay.style.position = 'absolute';
        textOverlay.style.zIndex = '2';
        textOverlay.style.transition = 'opacity 0.5s ease-in-out';

        // Appliquer le fond semi-transparent si nécessaire
        if (media.hasBackground) {
            textOverlay.style.background = 'rgba(0, 0, 0, 0.5)';
        }

        // Positionner le texte
        const positions = {
            'top-left': { top: '10px', left: '10px' },
            'top-center': { top: '10px', left: '50%', transform: 'translateX(-50%)' },
            'top-right': { top: '10px', right: '10px' },
            'center': { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' },
            'bottom-left': { bottom: '10px', left: '10px' },
            'bottom-center': { bottom: '10px', left: '50%', transform: 'translateX(-50%)' },
            'bottom-right': { bottom: '10px', right: '10px' }
        };

        const position = positions[media.textPosition] || positions['bottom-center'];
        Object.assign(textOverlay.style, position);
    }

    function scheduleNextMedia(section, duration = 5000) {
        if (section === 'cuisine') {
            if (cuisineTimeout) clearTimeout(cuisineTimeout);
            cuisineTimeout = setTimeout(() => {
                if (cuisineConfig.medias.length > 0) {
                    cuisineIndex = (cuisineIndex + 1) % cuisineConfig.medias.length;
                    showMedia('cuisine', cuisineConfig.medias[cuisineIndex]);
                }
            }, duration);
        } else if (section === 'direction') {
            if (directionTimeout) clearTimeout(directionTimeout);
            directionTimeout = setTimeout(() => {
                if (directionConfig.medias.length > 0) {
                    directionIndex = (directionIndex + 1) % directionConfig.medias.length;
                    showMedia('direction', directionConfig.medias[directionIndex]);
                }
            }, duration);
        }
    }

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Initialisation du diaporama...');
        // Charger immédiatement les configurations
        loadConfigurations();
        // Vérifier les mises à jour toutes les 30 secondes
        setInterval(loadConfigurations, 30000);

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
  </script>
</body>

</html>