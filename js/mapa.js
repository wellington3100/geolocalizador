// ===================================
// VARIABLES GLOBALES
// ===================================
let mapa;
let marcador;
let capaActual;
let latitudActual = 25.4232; // Saltillo, Coahuila (centro por defecto)
let longitudActual = -100.9948;
let minimapa;
let marcadorDistancia = null;
let lineaDistancia = null;

// ===================================
// CAPAS DE MAPAS DISPONIBLES
// ===================================
const capas = {
    streets: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxZoom: 19
    }),
    topo: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | &copy; <a href="https://opentopomap.org">OpenTopoMap</a>',
        maxZoom: 17
    })
};

// ===================================
// INICIALIZAR MAPA AL CARGAR LA PÁGINA
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    inicializarMapa();
    
    // Si hay parámetros en la URL (lat y lng), centrar ahí
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('lat') && urlParams.has('lng')) {
        const lat = parseFloat(urlParams.get('lat'));
        const lng = parseFloat(urlParams.get('lng'));
        mapa.setView([lat, lng], 15);
        moverMarcador(lat, lng);
    }
});

// ===================================
// FUNCIÓN: INICIALIZAR MAPA
// ===================================
function inicializarMapa() {
    // Crear el mapa centrado en Saltillo
    mapa = L.map('map').setView([latitudActual, longitudActual], 13);
    
    // Agregar capa inicial (streets)
    capaActual = capas.streets;
    capaActual.addTo(mapa);
    
    // Crear marcador arrastrable
    marcador = L.marker([latitudActual, longitudActual], {
        draggable: true,
        title: 'Arrastra para cambiar ubicación'
    }).addTo(mapa);
    
    marcador.bindPopup('<b>Tu ubicación seleccionada</b><br>Arrastra el marcador para cambiar').openPopup();
    
    // Actualizar coordenadas cuando se mueve el mapa
    mapa.on('move', function() {
        const centro = mapa.getCenter();
        actualizarDisplay(centro.lat, centro.lng);
    });
    
    // Actualizar coordenadas cuando se mueve el marcador
    marcador.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        actualizarDisplay(pos.lat, pos.lng);
        moverMarcador(pos.lat, pos.lng);
    });
    
    // Actualizar zoom display
    mapa.on('zoomend', function() {
        document.getElementById('zoom-display').textContent = mapa.getZoom();
    });
    
    // Click en el mapa para mover marcador
    mapa.on('click', function(e) {
        crearEfectoOnda(e.latlng.lat, e.latlng.lng);
        moverMarcador(e.latlng.lat, e.latlng.lng);
    });
    
    // Actualizar display inicial
    actualizarDisplay(latitudActual, longitudActual);
    
    // Inicializar minimapa si existe el elemento
    inicializarMinimapa();
}

// ===================================
// FUNCIÓN: CREAR EFECTO DE ONDA
// ===================================
function crearEfectoOnda(lat, lng) {
    const punto = mapa.latLngToContainerPoint([lat, lng]);
    const ripple = document.createElement('div');
    ripple.className = 'map-ripple';
    ripple.style.left = punto.x + 'px';
    ripple.style.top = punto.y + 'px';
    document.getElementById('map').appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 1000);
}

// ===================================
// FUNCIÓN: INICIALIZAR MINIMAPA
// ===================================
function inicializarMinimapa() {
    const minimapaEl = document.getElementById('minimapa-corner');
    if (!minimapaEl) return;
    
    minimapa = L.map('minimapa-corner', {
        dragging: false,
        zoomControl: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        touchZoom: false
    }).setView([latitudActual, longitudActual], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(minimapa);
    
    // Actualizar minimapa cuando se mueve el mapa principal
    mapa.on('move', function() {
        const centro = mapa.getCenter();
        minimapa.setView([centro.lat, centro.lng]);
    });
}

// ===================================
// FUNCIÓN: ACTUALIZAR DISPLAY DE COORDENADAS
// ===================================
function actualizarDisplay(lat, lng) {
    document.getElementById('lat-display').textContent = lat.toFixed(6);
    document.getElementById('lng-display').textContent = lng.toFixed(6);
    latitudActual = lat;
    longitudActual = lng;
    
    // Actualizar conversor de coordenadas
    actualizarConversorCoordenadas(lat, lng);
}

// ===================================
// FUNCIÓN: CONVERSOR DE COORDENADAS
// ===================================
function actualizarConversorCoordenadas(lat, lng) {
    const dmsElement = document.getElementById('coord-dms');
    const utmElement = document.getElementById('coord-utm');
    
    if (dmsElement) {
        dmsElement.textContent = convertirADMS(lat, lng);
    }
    
    if (utmElement) {
        utmElement.textContent = `UTM: ${lat.toFixed(2)}, ${lng.toFixed(2)}`;
    }
}

// ===================================
// FUNCIÓN: CONVERTIR A GRADOS, MINUTOS, SEGUNDOS
// ===================================
function convertirADMS(lat, lng) {
    const latDMS = decimalADMS(lat, lat >= 0 ? 'N' : 'S');
    const lngDMS = decimalADMS(lng, lng >= 0 ? 'E' : 'W');
    return `${latDMS}, ${lngDMS}`;
}

function decimalADMS(decimal, direccionPos, direccionNeg) {
    const absolute = Math.abs(decimal);
    const grados = Math.floor(absolute);
    const minutosDecimal = (absolute - grados) * 60;
    const minutos = Math.floor(minutosDecimal);
    const segundos = ((minutosDecimal - minutos) * 60).toFixed(2);
    
    const direccion = decimal >= 0 ? direccionPos : (direccionNeg || direccionPos);
    return `${grados}°${minutos}'${segundos}" ${direccion}`;
}

// ===================================
// FUNCIÓN: COPIAR COORDENADAS
// ===================================
function copiarCoordenadas(formato = 'decimal') {
    let texto = '';
    
    switch(formato) {
        case 'decimal':
            texto = `${latitudActual.toFixed(6)}, ${longitudActual.toFixed(6)}`;
            break;
        case 'dms':
            texto = convertirADMS(latitudActual, longitudActual);
            break;
        case 'separado':
            texto = `Latitud: ${latitudActual.toFixed(6)}\nLongitud: ${longitudActual.toFixed(6)}`;
            break;
    }
    
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
            title: '¡Coordenadas copiadas!'
        });
    }).catch(err => {
        console.error('Error al copiar:', err);
    });
}

// ===================================
// FUNCIÓN: MOVER MARCADOR
// ===================================
function moverMarcador(lat, lng) {
    marcador.setLatLng([lat, lng]);
    actualizarDisplay(lat, lng);
    marcador.openPopup();
}

// ===================================
// FUNCIÓN: OBTENER UBICACIÓN ACTUAL
// ===================================
function obtenerUbicacion() {
    if (navigator.geolocation) {
        const boton = event.target.closest('button');
        const textoOriginal = boton.innerHTML;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obteniendo ubicación...';
        boton.disabled = true;
        
        // Mostrar alerta de cargando
        Swal.fire({
            title: 'Obteniendo ubicación...',
            html: 'Por favor, espera un momento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                mapa.setView([lat, lng], 15);
                moverMarcador(lat, lng);
                
                marcador.setPopupContent('<b>¡Tu ubicación actual!</b><br>Precisión: ' + Math.round(position.coords.accuracy) + ' metros');
                marcador.openPopup();
                
                boton.innerHTML = textoOriginal;
                boton.disabled = false;
                
                // Alerta de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Ubicación encontrada!',
                    html: `
                        <p><strong>Latitud:</strong> ${lat.toFixed(6)}</p>
                        <p><strong>Longitud:</strong> ${lng.toFixed(6)}</p>
                        <p><strong>Precisión:</strong> ${Math.round(position.coords.accuracy)} metros</p>
                    `,
                    confirmButtonText: 'Perfecto',
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            function(error) {
                let mensaje = '';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        mensaje = 'Has denegado el permiso de ubicación. Por favor, permite el acceso en la configuración de tu navegador.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        mensaje = 'La información de ubicación no está disponible en este momento.';
                        break;
                    case error.TIMEOUT:
                        mensaje = 'Se agotó el tiempo de espera al intentar obtener tu ubicación.';
                        break;
                    default:
                        mensaje = 'Ocurrió un error desconocido al obtener tu ubicación.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Geolocalización',
                    text: mensaje,
                    confirmButtonText: 'Entendido'
                });
                
                boton.innerHTML = textoOriginal;
                boton.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Navegador no compatible',
            text: 'Tu navegador no soporta geolocalización. Intenta con Chrome, Firefox o Safari.',
            confirmButtonText: 'Entendido'
        });
    }
}

// ===================================
// FUNCIÓN: CAMBIAR CAPA DEL MAPA
// ===================================
function cambiarCapa(tipo) {
    // Remover capa actual
    mapa.removeLayer(capaActual);
    
    // Agregar nueva capa
    capaActual = capas[tipo];
    capaActual.addTo(mapa);
    
    // Actualizar botones activos
    document.querySelectorAll('.layer-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById('btn-' + tipo).classList.add('active');
    
    // Notificación pequeña
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
    
    let nombreCapa = tipo === 'streets' ? 'Calles' : tipo === 'satellite' ? 'Satélite' : 'Topográfico';
    
    Toast.fire({
        icon: 'success',
        title: `Vista cambiada a ${nombreCapa}`
    });
}

// ===================================
// FUNCIÓN: GUARDAR UBICACIÓN
// ===================================
function guardarUbicacion() {
    const descripcion = document.getElementById('descripcion').value.trim() || 'Sin descripción';
    
    const datos = {
        latitud: latitudActual,
        longitud: longitudActual,
        descripcion: descripcion
    };
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando ubicación...',
        html: 'Por favor, espera un momento',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar a PHP
    fetch('guardar_ubicacion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
    })
    .then(response => {
        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Respuesta del servidor no es JSON');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Ubicación guardada!',
                html: `
                    <p><strong>Descripción:</strong> ${descripcion}</p>
                    <p><strong>Latitud:</strong> ${latitudActual.toFixed(6)}</p>
                    <p><strong>Longitud:</strong> ${longitudActual.toFixed(6)}</p>
                `,
                confirmButtonText: 'Genial',
                showCancelButton: true,
                cancelButtonText: 'Ver historial',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#FF6B35'
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = 'historial.php';
                }
            });
            
            document.getElementById('descripcion').value = '';
            
            // Animación de éxito en el marcador
            marcador.setPopupContent('<b>✅ Ubicación guardada!</b><br>' + descripcion);
            marcador.openPopup();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: data.message || 'Ocurrió un error desconocido',
                confirmButtonText: 'Reintentar'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Verifica tu conexión a internet y que PHP esté funcionando correctamente.',
            confirmButtonText: 'Entendido',
            footer: '<small>Detalles: ' + error.message + '</small>'
        });
    });
}

// ===================================
// FUNCIÓN: BUSCAR DIRECCIÓN
// ===================================
function buscarDireccion() {
    const direccion = document.getElementById('searchInput').value.trim();
    
    if (!direccion) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo vacío',
            text: 'Por favor ingresa una ubicación a buscar',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Buscando ubicación...',
        html: `Buscando: <strong>${direccion}</strong>`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Usar API de Nominatim (OpenStreetMap)
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(direccion)}`;
    
    fetch(url)
    .then(response => response.json())
    .then(data => {
        if (data.length > 0) {
            const resultado = data[0];
            const lat = parseFloat(resultado.lat);
            const lng = parseFloat(resultado.lon);
            
            mapa.setView([lat, lng], 15);
            moverMarcador(lat, lng);
            
            marcador.setPopupContent('<b>' + resultado.display_name + '</b>');
            marcador.openPopup();
            
            Swal.fire({
                icon: 'success',
                title: '¡Ubicación encontrada!',
                html: `
                    <p style="text-align: left; padding: 0 20px;">
                        <strong>📍 Dirección:</strong><br>
                        ${resultado.display_name}
                    </p>
                    <hr style="margin: 15px 0;">
                    <p><strong>Latitud:</strong> ${lat.toFixed(6)}</p>
                    <p><strong>Longitud:</strong> ${lng.toFixed(6)}</p>
                `,
                confirmButtonText: 'Perfecto',
                confirmButtonColor: '#FF6B35'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'No se encontró',
                text: 'No se encontró la ubicación. Intenta con otro término de búsqueda más específico.',
                confirmButtonText: 'Reintentar'
            });
        }
    })
    .catch(error => {
        console.error('Error en búsqueda:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error en la búsqueda',
            text: 'No se pudo realizar la búsqueda. Verifica tu conexión a internet.',
            confirmButtonText: 'Entendido'
        });
    });
}

// ===================================
// FUNCIÓN: CALCULAR DISTANCIA
// ===================================
function activarCalculadoraDistancia() {
    if (marcadorDistancia) {
        // Desactivar modo
        mapa.off('click', clickCalcularDistancia);
        if (marcadorDistancia) mapa.removeLayer(marcadorDistancia);
        if (lineaDistancia) mapa.removeLayer(lineaDistancia);
        marcadorDistancia = null;
        lineaDistancia = null;
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
        Toast.fire({
            icon: 'info',
            title: 'Calculadora desactivada'
        });
        return;
    }
    
    Swal.fire({
        icon: 'info',
        title: 'Modo calculadora activado',
        text: 'Haz click en otro punto del mapa para calcular la distancia',
        confirmButtonText: 'Entendido'
    });
    
    mapa.on('click', clickCalcularDistancia);
}

function clickCalcularDistancia(e) {
    if (!marcadorDistancia) {
        marcadorDistancia = L.marker(e.latlng).addTo(mapa);
        return;
    }
    
    const puntoA = marcador.getLatLng();
    const puntoB = e.latlng;
    
    // Calcular distancia usando fórmula de Haversine
    const distancia = mapa.distance(puntoA, puntoB);
    const distanciaKm = (distancia / 1000).toFixed(2);
    const distanciaMetros = distancia.toFixed(0);
    
    // Dibujar línea
    if (lineaDistancia) mapa.removeLayer(lineaDistancia);
    lineaDistancia = L.polyline([puntoA, puntoB], {
        color: '#FF6B35',
        weight: 3,
        opacity: 0.7
    }).addTo(mapa);
    
    Swal.fire({
        icon: 'success',
        title: 'Distancia calculada',
        html: `
            <div style="font-size: 3rem; font-weight: 900; color: #FF6B35; margin: 20px 0;">
                ${distanciaKm} km
            </div>
            <p>${distanciaMetros} metros</p>
        `,
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#FF6B35'
    });
    
    // Limpiar
    mapa.off('click', clickCalcularDistancia);
    if (marcadorDistancia) mapa.removeLayer(marcadorDistancia);
    marcadorDistancia = null;
}

// ===================================
// COMPARTIR UBICACIÓN POR QR
// ===================================
function compartirQR() {
    const url = `${window.location.origin}${window.location.pathname}?lat=${latitudActual}&lng=${longitudActual}`;
    
    Swal.fire({
        title: 'Código QR de ubicación',
        html: `
            <div id="qrcode" style="display: flex; justify-content: center; margin: 20px 0;"></div>
            <p><small>${url}</small></p>
        `,
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#FF6B35',
        didOpen: () => {
            // Aquí podrías usar una librería de QR como qrcodejs
            document.getElementById('qrcode').innerHTML = `
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(url)}" alt="QR Code">
            `;
        }
    });
}