<?php
session_start();

require_once 'libs/conexion.php';
require_once 'libs/sessionCheck.php';

$mensajesPersonalizados = [
    'reporte_creado'      => 'Registro creado exitosamente', 
    'registro_exito'      => 'Se ha creado el usuario exitosamente'   
];

// Obtener los reportes de los últimos 15 días junto con la máxima gravedad
$sql = "SELECT r.FOLIO, r.DIRECCION, r.DESCRIPCION, r.LATITUD, r.LONGITUD, r.FECHA_CREACION,
               c.NOMBRE AS COLONIA,
               GROUP_CONCAT(tc.NOMBRE SEPARATOR ', ') AS CRIMENES,
               MAX(tc.GRAVEDAD) AS MAX_GRAVEDAD
        FROM reportes r
        LEFT JOIN colonias c ON r.COLONIA = c.CODIGO_POSTAL
        LEFT JOIN crimenes cr ON r.FOLIO = cr.FOLIO
        LEFT JOIN tipos_crimen tc ON cr.CVE_TIPO_CRIMEN = tc.CVE_TIPO_CRIMEN
        WHERE r.FECHA_CREACION >= NOW() - INTERVAL 15 DAY
        GROUP BY r.FOLIO
        ORDER BY r.FECHA_CREACION DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reportesRecientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reportesJson = json_encode($reportesRecientes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

// Cargar catálogo de colonias
$stmtCol = $pdo->prepare("SELECT CODIGO_POSTAL, NOMBRE FROM colonias ORDER BY NOMBRE ASC");
$stmtCol->execute();
$colonias = $stmtCol->fetchAll(PDO::FETCH_ASSOC);

// Cargar catálogo de tipos de crimen
$stmtTipos = $pdo->prepare("SELECT CVE_TIPO_CRIMEN, NOMBRE, GRAVEDAD FROM tipos_crimen ORDER BY NOMBRE ASC");
$stmtTipos->execute();
$tiposCrimen = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="resources/img/CrimeDetectorLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/variables.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/generico.css">
    <link rel="stylesheet" href="styles/mapa.css">
    
    <!-- Leaflet CSS y JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
            
    <title>Mapa | CrimeDetector</title>
</head>
<body>    
    <?php require_once "templates/header.php"; ?>

    <main class="mapa-page-container">
        <!-- Barra flotante de instrucciones y controles -->
        <div class="mapa-control-bar">
            <div class="instruccion-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Haz clic en cualquier punto del mapa para <strong>crear un reporte</strong></span>
            </div>
            <div class="instruccion-divider"></div>
            <div class="instruccion-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <span>Selecciona un marcador para <strong>ver detalles</strong></span>
            </div>
        </div>

        <?php require_once "templates/mensajes_popover.php"; ?>

        <!-- Contenedor del Mapa -->
        <div id="map"></div>
    </main>
    <!-- MODAL / FORMULARIO FLOTANTE -->    
    <div id="modalReporte" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            
            <!-- Header del Modal -->
            <div class="modal-header">
                <div>
                    <h3>Nuevo Reporte de Incidente</h3>
                    <p class="modal-subtitle">Ingresa los detalles del evento registrado</p>
                </div>
                <button type="button" id="cerrarModalBtn" class="btn-cerrar-modal">&times;</button>
            </div>
            
            <!-- Formulario -->
            <form action="controllers/procesarReporteForm.php" method="POST" class="form-reporte-mapa">
                <input type="hidden" name="accion" value="crear_reporte">
                <input type="hidden" name="usuario" value="<?= $_SESSION['correo'] ?>">
                
                <div class="modal-body">
                    <!-- Coordenadas -->
                    <div class="grupo-coordenadas">
                        <div class="campo-coordenada">
                            <label>Latitud</label>
                            <input type="text" id="latitud" name="latitud" readonly required placeholder="0.000000">
                        </div>
                        <div class="campo-coordenada">
                            <label>Longitud</label>
                            <input type="text" id="longitud" name="longitud" readonly required placeholder="0.000000">
                        </div>
                    </div>

                    <!-- Colonia / CP -->
                    <div class="campo">
                        <label for="colonia">Colonia / Código Postal</label>
                        <div class="select-custom-wrapper">
                            <select name="colonia" id="colonia" required>
                                <option value="">-- Selecciona Colonia --</option>
                                <?php foreach ($colonias as $c): ?>
                                    <option value="<?= htmlspecialchars($c['CODIGO_POSTAL']) ?>">
                                        <?= htmlspecialchars($c['NOMBRE']) ?> (CP: <?= htmlspecialchars($c['CODIGO_POSTAL']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="campo">
                        <label for="direccion">Dirección / Referencia</label>
                        <input type="text" id="direccion" name="direccion" placeholder="Ej. Calle Mina #123, esquina con Zaragoza" required>
                    </div>

                    <!-- Descripción -->
                    <div class="campo">
                        <label for="descripcion">Descripción del Suceso</label>
                        <textarea id="descripcion" name="descripcion" rows="3" placeholder="Describe brevemente lo ocurrido..." required></textarea>
                    </div>

                    <!-- Checkboxes de Tipos de Crimen -->
                    <div class="campo">
                        <label class="label-seccion">Tipos de Crimen Involucrados</label>
                        <div class="grid-checkboxes-crimen">
                            <?php foreach ($tiposCrimen as $tipo): ?>
                                <label class="item-chip">
                                    <input type="checkbox" name="crimenes[]" value="<?= $tipo['CVE_TIPO_CRIMEN'] ?>">
                                    <span class="chip-content">
                                        <span class="chip-indicator"></span>
                                        <?= htmlspecialchars($tipo['NOMBRE']) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer con Botones -->
                <div class="modal-footer">
                    <button type="button" id="btnCancelar" class="btn-modal btn-cancelar">Cancelar</button>
                    <button type="submit" class="btn-modal btn-guardar">Guardar Reporte</button>
                </div>
            </form>
        </div>
    </div>
    <script>        
        var map = L.map('map').setView([17.9928, -92.9255], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        function obtenerColorGravedad(gravedad) {
            switch (parseInt(gravedad)) {
                case 1:
                    return '#2563eb'; 
                case 2:
                    return '#eab308'; 
                case 3:
                    return '#f97316'; 
                case 4:
                    return '#dc2626'; 
                case 5:
                    return '#18181b'; 
                default:
                    return '#6b7280'; 
            }
        }

        const reportes = <?= $reportesJson ?>;

        reportes.forEach(reporte => {
            const lat = parseFloat(reporte.LATITUD);
            const lng = parseFloat(reporte.LONGITUD);

            if (!isNaN(lat) && !isNaN(lng)) {
                const fecha = new Date(reporte.FECHA_CREACION).toLocaleDateString('es-MX', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const crimenesTexto = reporte.CRIMENES ? reporte.CRIMENES : 'Sin clasificar';
                const color = obtenerColorGravedad(reporte.MAX_GRAVEDAD);

                const popupContent = `
                    <div class="popup-reporte">
                        <h4>Reporte #${reporte.FOLIO}</h4>
                        <p class="popup-crimenes">⚠️ ${crimenesTexto}</p>
                        <p><strong>Gravedad Máx:</strong> Nivel ${reporte.MAX_GRAVEDAD || 'N/A'}</p>
                        <p><strong>Colonia:</strong> ${reporte.COLONIA || 'N/A'}</p>
                        <p><strong>Dirección:</strong> ${reporte.DIRECCION}</p>
                        <p><strong>Descripción:</strong> ${reporte.DESCRIPCION}</p>
                        <p style="color: #666; font-size: 0.75rem; margin-top: 6px;">📅 ${fecha}</p>
                    </div>
                `;

                // Crear marcador circular personalizado
                L.circleMarker([lat, lng], {
                    radius: 9,              // Tamaño del punto
                    fillColor: color,       // Color interno según la gravedad
                    color: '#ffffff',       // Borde blanco para resaltar sobre el mapa
                    weight: 2,              // Grosor del borde
                    opacity: 1,             // Opacidad del borde
                    fillOpacity: 0.85       // Opacidad del relleno
                })
                .addTo(map)
                .bindPopup(popupContent);
            }
        });

        // Crear leyenda en el mapa
        const leyenda = L.control({ position: 'bottomright' });

        leyenda.onAdd = function () {
            const div = L.DomUtil.create('div', 'leyenda-mapa');
            div.innerHTML = `
                <h4>Gravedad</h4>
                <div><span style="background:#2563eb;"></span> 1 - Leve (Azul)</div>
                <div><span style="background:#eab308;"></span> 2 - Moderado (Amarillo)</div>
                <div><span style="background:#f97316;"></span> 3 - Medio (Naranja)</div>
                <div><span style="background:#dc2626;"></span> 4 - Grave (Rojo)</div>
                <div><span style="background:#18181b;"></span> 5 - Crítico (Negro)</div>
            `;
            return div;
        };

        leyenda.addTo(map);

        // Variable para almacenar el marcador temporal del nuevo reporte
        let marcadorNuevoReporte = null;

        const modal = document.getElementById('modalReporte');
        const inputLat = document.getElementById('latitud');
        const inputLng = document.getElementById('longitud');
        const btnCerrar = document.getElementById('cerrarModalBtn');
        const btnCancelar = document.getElementById('btnCancelar');

        // Evento al hacer clic en cualquier lugar del mapa
        map.on('click', function (e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            // Asignar valores a los inputs del formulario
            inputLat.value = lat;
            inputLng.value = lng;

            // Si ya había un marcador temporal previo, lo quitamos
            if (marcadorNuevoReporte) {
                map.removeLayer(marcadorNuevoReporte);
            }

            // Poner un marcador temporal verde donde el usuario dio clic
            marcadorNuevoReporte = L.circleMarker([lat, lng], {
                radius: 11,
                fillColor: '#16a34a', // Verde para indicar selección
                color: '#ffffff',
                weight: 3,
                fillOpacity: 0.9
            }).addTo(map);

            // --- GEOCODIFICACIÓN INVERSA (Autoselección de Colonia) ---
            const selectColonia = document.getElementById('colonia');
            const selectDireccion = document.getElementById('direccion');
            selectColonia.disabled = true; // Deshabilitar temporalmente mientras busca
            selectDireccion.disabled = true; // Deshabilitar temporalmente mientras busca

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    selectColonia.disabled = false;
                    selectDireccion.disabled = false;
                    
                    if (data && data.address) {
                        // Obtener el código postal o nombre de la colonia que devuelve OpenStreetMap
                        const cpDetectado = data.address.postcode;
                        const coloniaDetectada = data.address.neighbourhood || data.address.suburb || data.address.quarter;
                        const direccionDetectada = data.display_name;

                        // 1. Intentar coincidencia por Código Postal
                        let opcionEncontrada = Array.from(selectColonia.options).find(opt => opt.value === cpDetectado);                    
                        // 2. Si no coincide por CP, buscar por coincidencia de nombre en el texto de las opciones
                        if (!opcionEncontrada && coloniaDetectada) {
                            opcionEncontrada = Array.from(selectColonia.options).find(opt => 
                                opt.text.toLowerCase().includes(coloniaDetectada.toLowerCase())
                            );
                        }
                        
                        // Asignar el valor si se encontró una coincidencia en el select
                        if (opcionEncontrada) {
                            selectColonia.value = opcionEncontrada.value;
                        } else {
                            // Si Nominatim devuelve coordenadas específicas de la colonia usamos data.lat/data.lon, 
                            // de lo contrario usamos lat/lng del clic en el mapa.
                            const latColonia = parseFloat(data.lat) || parseFloat(lat);
                            const lngColonia = parseFloat(data.lon) || parseFloat(lng);

                            // Registrar automáticamente vía AJAX guardando latitud y longitud
                            fetch('controllers/registrarColoniaAuto.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    codigo_postal: cpDetectado,
                                    nombre: coloniaDetectada,
                                    latitud: latColonia,
                                    longitud: lngColonia
                                })
                            })
                            .then(res => res.json())
                            .then(resData => {
                                console.log("RegistrarColoniaAutoRes: " + JSON.stringify(resData, null, 2));
                                if (resData.success) {
                                    // Crear la opción nueva con sus atributos data-lat y data-lng
                                    const nuevaOpcion = new Option(
                                        `${resData.nombre} (CP: ${resData.codigo_postal})`, 
                                        resData.codigo_postal, 
                                        true, 
                                        true
                                    );
                                    nuevaOpcion.setAttribute('data-lat', resData.latitud);
                                    nuevaOpcion.setAttribute('data-lng', resData.longitud);

                                    selectColonia.add(nuevaOpcion);
                                }
                            })
                            .catch(err => console.error("Error al registrar colonia:", err));
                        }
                        if (direccionDetectada) {
                            selectDireccion.value = direccionDetectada;
                        } else {
                            selectDireccion.value = "";
                        }
                    }
                })
                .catch(err => {
                    selectColonia.disabled = false;
                    console.error("Error al obtener la colonia:", err);
                });

            // Mostrar el formulario modal
            modal.style.display = 'flex';
        });

        // Función para cerrar el modal y remover el pin temporal
        function ocultarModal() {
            modal.style.display = 'none';
            if (marcadorNuevoReporte) {
                map.removeLayer(marcadorNuevoReporte);
                marcadorNuevoReporte = null;
            }
        }

        btnCerrar.addEventListener('click', ocultarModal);
        btnCancelar.addEventListener('click', ocultarModal);
    </script>          
</body>
</html>