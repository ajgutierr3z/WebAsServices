<?php

class ReporteDAO {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerTodos(): array {
        $sql = "SELECT r.*, 
                    u.NOMBRE AS NOMBRE_USUARIO, 
                    c.NOMBRE AS NOMBRE_COLONIA,
                    GROUP_CONCAT(tc.NOMBRE SEPARATOR '||') AS CRIMENES_LISTA
                FROM reportes r
                LEFT JOIN usuarios u ON r.USUARIO = u.CORREO
                LEFT JOIN colonias c ON r.COLONIA = c.CODIGO_POSTAL
                LEFT JOIN crimenes cr ON r.FOLIO = cr.FOLIO
                LEFT JOIN tipos_crimen tc ON cr.CVE_TIPO_CRIMEN = tc.CVE_TIPO_CRIMEN
                GROUP BY r.FOLIO
                ORDER BY r.FECHA_CREACION DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorFolio(int $folio): ?array {
        $sql = "SELECT * FROM reportes WHERE FOLIO = :folio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['folio' => $folio]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function registrarReporte(string $usuario, int $colonia, string $direccion, float $lat, float $lng, string $descripcion, array $crimenes = []): bool {
        try {
            // Iniciar transacción para asegurar consistencia
            $this->pdo->beginTransaction();

            // 1. Insertar el reporte
            $sqlReporte = "INSERT INTO reportes (USUARIO, COLONIA, DIRECCION, LATITUD, LONGITUD, DESCRIPCION, FECHA_CREACION) 
                        VALUES (:usuario, :colonia, :direccion, :lat, :lng, :descripcion, NOW())";
            
            $stmt = $this->pdo->prepare($sqlReporte);
            $stmt->execute([
                ':usuario'     => $usuario,
                ':colonia'     => $colonia,
                ':direccion'   => $direccion,
                ':lat'         => $lat,
                ':lng'         => $lng,
                ':descripcion' => $descripcion
            ]);

            // 2. Obtener el FOLIO recién generado por el AUTO_INCREMENT
            $folioGenerado = $this->pdo->lastInsertId();

            // 3. Insertar cada crimen seleccionado en la tabla pivote 'crimenes'
            if (!empty($crimenes) && $folioGenerado > 0) {
                $sqlCrimen = "INSERT INTO crimenes (FOLIO, CVE_TIPO_CRIMEN) VALUES (:folio, :cve_tipo)";
                $stmtCrimen = $this->pdo->prepare($sqlCrimen);

                foreach ($crimenes as $cveTipo) {
                    $stmtCrimen->execute([
                        ':folio'    => $folioGenerado,
                        ':cve_tipo' => (int) $cveTipo
                    ]);
                }
            }

            // Si todo salió bien, confirmamos la transacción
            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            // Si algo falla, revertimos todos los cambios
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function actualizarReporte(int $folio, string $usuario, int $colonia, string $direccion, float $lat, float $lng, string $descripcion): bool {
        $sql = "UPDATE reportes 
                SET USUARIO = :usuario, COLONIA = :colonia, DIRECCION = :direccion, 
                    LATITUD = :lat, LONGITUD = :lng, DESCRIPCION = :descripcion 
                WHERE FOLIO = :folio";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'usuario'     => $usuario,
            'colonia'     => $colonia,
            'direccion'   => $direccion,
            'lat'         => $lat,
            'lng'         => $lng,
            'descripcion' => $descripcion,
            'folio'       => $folio
        ]);
    }

    public function borrarReporte($folio) {
        try {
            // Iniciamos la transacción para asegurar consistencia
            $this->pdo->beginTransaction();

            // 1. Eliminar los crímenes asociados a este reporte
            $sqlCrimenes = "DELETE FROM crimenes WHERE FOLIO = :folio";
            $stmtCrimenes = $this->pdo->prepare($sqlCrimenes);
            $stmtCrimenes->execute(['folio' => $folio]);

            // 2. Eliminar el reporte
            $sqlReporte = "DELETE FROM reportes WHERE FOLIO = :folio";
            $stmtReporte = $this->pdo->prepare($sqlReporte);
            $stmtReporte->execute(['folio' => $folio]);

            // Confirmar los cambios
            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            // En caso de error, revertimos todo
            $this->pdo->rollBack();
            throw $e;
        }
    }
}