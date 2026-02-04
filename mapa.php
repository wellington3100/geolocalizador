<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Interactivo - Geolocalizador Sunset</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mapa.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&family=Space+Grotesk:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>
                <a href="index.html">
                    <i class="fas fa-map-marked-alt"></i> Geolocalizador
                </a>
            </h1>
            <ul class="nav-menu">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="mapa.php" class="active">Mapa Interactivo</a></li>
                <li><a href="historial.php">Historial</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Breadcrumbs -->
        <div class="breadcrumbs">
            <a href="index.html"><i class="fas fa-home"></i> Inicio</a>
            <span>/</span>
            <span>Mapa Interactivo</span>
        </div>

        <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 2.5rem; background: var(--gradient-sunset); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 900; margin-bottom: 2rem; text-align: center;">
            <i class="fas fa-globe"></i> Mapa Interactivo con Geolocalización
        </h2>
        
        <div class="map-wrapper">
            <!-- Panel Lateral -->
            <div class="map-controls">
                <h3><i class="fas fa-sliders-h"></i> Controles del Mapa</h3>
                
                <!-- Tipo de Mapa -->
                <div class="control-group">
                    <label><i class="fas fa-layer-group"></i> Tipo de Mapa:</label>
                    <div class="layer-selector">
                        <button class="layer-btn active" onclick="cambiarCapa('streets')" id="btn-streets">
                            <i class="fas fa-map"></i>
                            <span>Calles</span>
                        </button>
                        <button class="layer-btn" onclick="cambiarCapa('satellite')" id="btn-satellite">
                            <i class="fas fa-satellite"></i>
                            <span>Satélite</span>
                        </button>
                        <button class="layer-btn" onclick="cambiarCapa('topo')" id="btn-topo">
                            <i class="fas fa-mountain"></i>
                            <span>Topográfico</span>
                        </button>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="obtenerUbicacion()">
                        <i class="fas fa-location-arrow"></i> Mi Ubicación
                    </button>
                    <button class="btn btn-success" onclick="guardarUbicacion()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button class="btn btn-info" onclick="buscarDireccion()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>

                <!-- Buscar Ubicación -->
                <div class="control-group">
                    <label><i class="fas fa-search-location"></i> Buscar ubicación:</label>
                    <input type="text" id="searchInput" placeholder="Ej: Saltillo, Coahuila" 
                           onkeypress="if(event.keyCode==13) buscarDireccion()">
                </div>

                <!-- Descripción -->
                <div class="control-group">
                    <label><i class="fas fa-comment"></i> Descripción (opcional):</label>
                    <input type="text" id="descripcion" placeholder="Ej: Mi casa, Trabajo, Restaurante favorito...">
                </div>

                <!-- Coordenadas Actuales -->
                <div class="coords-display">
                    <strong><i class="fas fa-crosshairs"></i> Coordenadas Actuales:</strong><br>
                    <span>Lat: <span id="lat-display">--</span></span><br>
                    <span>Lng: <span id="lng-display">--</span></span>
                    <div class="zoom-badge">
                        <i class="fas fa-search-plus"></i>
                        <span>Zoom: <span id="zoom-display">13</span></span>
                    </div>
                </div>

                <!-- Conversor de Coordenadas -->
                <div class="coord-converter">
                    <h4><i class="fas fa-exchange-alt"></i> Conversor de Coordenadas</h4>
                    <div class="coord-formats">
                        <div class="coord-format">
                            <div>
                                <strong>DMS:</strong><br>
                                <small id="coord-dms">--</small>
                            </div>
                            <button onclick="copiarCoordenadas('dms')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="coord-format">
                            <div>
                                <strong>Decimal:</strong><br>
                                <small id="coord-decimal">--</small>
                            </div>
                            <button onclick="copiarCoordenadas('decimal')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Herramientas Adicionales -->
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="activarCalculadoraDistancia()">
                        <i class="fas fa-ruler"></i> Medir Distancia
                    </button>
                    <button class="btn btn-info" onclick="compartirQR()">
                        <i class="fas fa-qrcode"></i> Compartir QR
                    </button>
                </div>
            </div>

            <!-- Mapa -->
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="info-box">
            <h4><i class="fas fa-info-circle"></i> Instrucciones de uso:</h4>
            <ul>
                <li><strong>Navegar:</strong> Arrastra el mapa con el mouse o toca y desliza en móvil</li>
                <li><strong>Zoom:</strong> Usa la rueda del mouse, los botones +/- o pellizca en móvil</li>
                <li><strong>Mi Ubicación:</strong> Click en el botón para centrar en tu ubicación actual</li>
                <li><strong>Cambiar vista:</strong> Alterna entre vista de calles, satélite o topográfico</li>
                <li><strong>Guardar:</strong> Agrega una descripción y guarda ubicaciones importantes</li>
                <li><strong>Buscar:</strong> Escribe un lugar en el buscador y presiona Enter</li>
                <li><strong>Marcar:</strong> Haz click en cualquier punto del mapa o arrastra el marcador</li>
                <li><strong>Medir:</strong> Usa la calculadora de distancia para medir entre dos puntos</li>
                <li><strong>Compartir:</strong> Genera un código QR para compartir la ubicación actual</li>
            </ul>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2024 Geolocalizador Sunset Edition - Sistema de Ubicación Interactivo</p>
        </div>
    </footer>

    <script>
        // Actualizar coordenadas decimal en tiempo real
        function actualizarCoordenadasDecimal() {
            const coordDecimal = document.getElementById('coord-decimal');
            if (coordDecimal) {
                coordDecimal.textContent = `${latitudActual.toFixed(6)}, ${longitudActual.toFixed(6)}`;
            }
        }
        
        // Override de la función actualizarDisplay original
        const actualizarDisplayOriginal = actualizarDisplay;
        actualizarDisplay = function(lat, lng) {
            actualizarDisplayOriginal(lat, lng);
            actualizarCoordenadasDecimal();
        };
    </script>
    <script src="js/mapa.js"></script>
</body>
</html>