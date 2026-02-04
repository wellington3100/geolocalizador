<?php
// Incluir la conexión
require_once 'conexion.php';

// Obtener todas las ubicaciones
try {
    $sql = "SELECT * FROM ubicaciones ORDER BY fecha_registro DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $ubicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ubicaciones = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - Geolocalizador Sunset</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/historial.css">
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
            <li><a href="mapa.php">Mapa Interactivo</a></li>
            <li><a href="historial.php" class="active">Historial</a></li>
        </ul>
    </div>
</nav>

    <div class="container">
        <!-- Header con Filtros -->
        <div class="historial-header">
            <h2><i class="fas fa-history"></i> Historial de Ubicaciones</h2>
            <p>Gestiona y explora todas tus ubicaciones guardadas</p>
            
            <div class="filters-container">
                <div class="filter-group">
                    <label>Buscar:</label>
                    <input type="text" id="filter-search" placeholder="Buscar por descripción...">
                </div>
                <div class="filter-group">
                    <label>Ordenar por:</label>
                    <select id="filter-sort">
                        <option value="recent">Más recientes</option>
                        <option value="oldest">Más antiguas</option>
                        <option value="name-asc">Nombre A-Z</option>
                        <option value="name-desc">Nombre Z-A</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn" onclick="aplicarFiltros()">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button class="filter-btn clear" onclick="limpiarFiltros()">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar ubicaciones: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (count($ubicaciones) > 0): ?>
            <!-- Estadísticas -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-map-pin"></i>
                    <h3><?php echo count($ubicaciones); ?></h3>
                    <p>Ubicaciones guardadas</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar"></i>
                    <h3><?php echo date('d/m/Y', strtotime($ubicaciones[0]['fecha_registro'])); ?></h3>
                    <p>Última ubicación</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo date('H:i', strtotime($ubicaciones[0]['fecha_registro'])); ?></h3>
                    <p>Hora del último registro</p>
                </div>
            </div>

            <!-- Toggle de Vista -->
            <div class="view-toggle">
                <button class="view-btn active" onclick="cambiarVista('list')" id="view-list">
                    <i class="fas fa-list"></i> Vista Lista
                </button>
                <button class="view-btn" onclick="cambiarVista('gallery')" id="view-gallery">
                    <i class="fas fa-th"></i> Vista Galería
                </button>
            </div>

            <!-- Vista de Lista -->
            <div class="historial-container" id="lista-ubicaciones">
                <?php foreach ($ubicaciones as $ub): ?>
                    <div class="ubicacion-card" data-descripcion="<?php echo htmlspecialchars($ub['descripcion']); ?>" data-fecha="<?php echo $ub['fecha_registro']; ?>">
                        <h3><i class="fas fa-map-pin"></i> <?php echo htmlspecialchars($ub['descripcion']); ?></h3>
                        
                        <div class="ubicacion-info">
                            <i class="fas fa-compass"></i> 
                            <strong>Latitud:</strong> <?php echo $ub['latitud']; ?>
                        </div>
                        
                        <div class="ubicacion-info">
                            <i class="fas fa-compass"></i> 
                            <strong>Longitud:</strong> <?php echo $ub['longitud']; ?>
                        </div>
                        
                        <div class="ubicacion-info">
                            <i class="fas fa-calendar"></i> 
                            <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($ub['fecha_registro'])); ?>
                        </div>

                        <div id="minimap-<?php echo $ub['id']; ?>" class="minimap"></div>

                        <div class="ubicacion-actions">
                            <button class="btn btn-primary btn-small" 
                                    onclick="verEnMapa(<?php echo $ub['latitud']; ?>, <?php echo $ub['longitud']; ?>)">
                                <i class="fas fa-eye"></i> Ver en Mapa
                            </button>
                            <button class="btn btn-info btn-small" 
                                    onclick="copiarCoordenadas(<?php echo $ub['latitud']; ?>, <?php echo $ub['longitud']; ?>)">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                            <button class="btn btn-danger btn-small" 
                                    onclick="eliminarUbicacion(<?php echo $ub['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>

                        <script>
                            (function() {
                                const minimap = L.map('minimap-<?php echo $ub['id']; ?>', {
                                    dragging: false,
                                    zoomControl: false,
                                    scrollWheelZoom: false,
                                    doubleClickZoom: false,
                                    touchZoom: false
                                }).setView([<?php echo $ub['latitud']; ?>, <?php echo $ub['longitud']; ?>], 14);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(minimap);

                                L.marker([<?php echo $ub['latitud']; ?>, <?php echo $ub['longitud']; ?>])
                                    .addTo(minimap)
                                    .bindPopup('<?php echo htmlspecialchars($ub['descripcion']); ?>');
                            })();
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Vista de Galería (oculta por defecto) -->
            <div class="gallery-view" id="galeria-ubicaciones" style="display: none;">
                <?php foreach ($ubicaciones as $ub): ?>
                    <div class="gallery-item">
                        <div class="gallery-map" id="gallery-map-<?php echo $ub['id']; ?>"></div>
                        <div class="gallery-info">
                            <h4><?php echo htmlspecialchars($ub['descripcion']); ?></h4>
                            <div class="gallery-coords">
                                <span><?php echo $ub['latitud']; ?></span>
                                <span><?php echo $ub['longitud']; ?></span>
                            </div>
                            <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($ub['fecha_registro'])); ?></p>
                            <div class="gallery-actions">
                                <button class="btn btn-primary btn-small" onclick="verEnMapa(<?php echo $ub['latitud']; ?>, <?php echo $ub['longitud']; ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-danger btn-small" onclick="eliminarUbicacion(<?php echo $ub['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <script>
                            (function() {
                                const galleryMap = L.map('gallery-map-<?php echo $ub['id']; ?>', {
                                    dragging: false,
                                    zoomControl: false,
                                    scrollWheelZoom: false,
                                    doubleClickZoom: false,
                                    touchZoom: false
                                }).setView([<?php echo $ub['latitud']; ?>, <?php echo $ub['longitud']; ?>], 13);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(galleryMap);
                                L.marker([<?php echo $ub['latitud']; ?>, <?php echo $ub['longitud']; ?>]).addTo(galleryMap);
                            })();
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="no-ubicaciones">
                <i class="fas fa-map-marked-alt"></i>
                <h3>No hay ubicaciones guardadas</h3>
                <p>Ve al mapa interactivo y guarda tus primeras ubicaciones</p>
                <a href="mapa.php" class="btn btn-primary btn-large">
                    <i class="fas fa-map"></i> Ir al Mapa
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2024 Geolocalizador Sunset Edition - Sistema de Ubicación Interactivo</p>
        </div>
    </footer>

    <script>
        let vistaActual = 'list';

        function cambiarVista(vista) {
            vistaActual = vista;
            
            const lista = document.getElementById('lista-ubicaciones');
            const galeria = document.getElementById('galeria-ubicaciones');
            const btnList = document.getElementById('view-list');
            const btnGallery = document.getElementById('view-gallery');
            
            if (vista === 'list') {
                lista.style.display = 'grid';
                galeria.style.display = 'none';
                btnList.classList.add('active');
                btnGallery.classList.remove('active');
            } else {
                lista.style.display = 'none';
                galeria.style.display = 'grid';
                btnList.classList.remove('active');
                btnGallery.classList.add('active');
            }
        }

        function aplicarFiltros() {
            const busqueda = document.getElementById('filter-search').value.toLowerCase();
            const ordenar = document.getElementById('filter-sort').value;
            
            const cards = Array.from(document.querySelectorAll('.ubicacion-card'));
            
            // Filtrar
            cards.forEach(card => {
                const descripcion = card.dataset.descripcion.toLowerCase();
                if (descripcion.includes(busqueda)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Ordenar
            const container = document.getElementById('lista-ubicaciones');
            const cardsVisibles = cards.filter(card => card.style.display !== 'none');
            
            cardsVisibles.sort((a, b) => {
                if (ordenar === 'recent') {
                    return new Date(b.dataset.fecha) - new Date(a.dataset.fecha);
                } else if (ordenar === 'oldest') {
                    return new Date(a.dataset.fecha) - new Date(b.dataset.fecha);
                } else if (ordenar === 'name-asc') {
                    return a.dataset.descripcion.localeCompare(b.dataset.descripcion);
                } else if (ordenar === 'name-desc') {
                    return b.dataset.descripcion.localeCompare(a.dataset.descripcion);
                }
            });
            
            cardsVisibles.forEach(card => container.appendChild(card));
        }

        function limpiarFiltros() {
            document.getElementById('filter-search').value = '';
            document.getElementById('filter-sort').value = 'recent';
            
            document.querySelectorAll('.ubicacion-card').forEach(card => {
                card.style.display = 'block';
            });
            
            aplicarFiltros();
        }

        function verEnMapa(lat, lng) {
            window.location.href = `mapa.php?lat=${lat}&lng=${lng}`;
        }

        function copiarCoordenadas(lat, lng) {
            const texto = `Latitud: ${lat}, Longitud: ${lng}`;
            navigator.clipboard.writeText(texto).then(() => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'success',
                    title: '¡Coordenadas copiadas al portapapeles!'
                });
            }).catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al copiar',
                    text: 'No se pudieron copiar las coordenadas al portapapeles',
                    confirmButtonText: 'Entendido'
                });
            });
        }

        function eliminarUbicacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch('eliminar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminada!',
                                text: 'La ubicación ha sido eliminada correctamente',
                                confirmButtonText: 'Perfecto',
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonText: 'Entendido'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo eliminar la ubicación',
                            confirmButtonText: 'Entendido'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>