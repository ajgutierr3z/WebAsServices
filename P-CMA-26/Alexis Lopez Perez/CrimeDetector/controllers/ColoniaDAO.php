<?php

class ColoniaDAO {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerTodas(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM colonias ORDER BY CODIGO_POSTAL ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCodigoPostal(int $cp): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM colonias WHERE CODIGO_POSTAL = :cp");
        $stmt->execute(['cp' => $cp]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function registrarColonia(int $cp, string $nombre, float $latitud, float $longitud): bool {
        $stmt = $this->pdo->prepare("INSERT INTO colonias (CODIGO_POSTAL, NOMBRE, LATITUD, LONGITUD) VALUES (:cp, :nombre, :lat, :lng)");
        return $stmt->execute([
            'cp'     => $cp,
            'nombre' => $nombre,
            'lat'    => $latitud,
            'lng'    => $longitud
        ]);
    }

    public function actualizarColonia(int $cp, string $nombre, float $latitud, float $longitud): bool {
        $stmt = $this->pdo->prepare("UPDATE colonias SET NOMBRE = :nombre, LATITUD = :lat, LONGITUD = :lng WHERE CODIGO_POSTAL = :cp");
        return $stmt->execute([
            'nombre' => $nombre,
            'lat'    => $latitud,
            'lng'    => $longitud,
            'cp'     => $cp
        ]);
    }

    public function borrarColonia($codigoPostal) {
        try {
            $this->pdo->beginTransaction();

            // 1. Eliminar los crímenes asociados a los reportes de esta colonia
            $sqlCrimenes = "DELETE FROM crimenes 
                            WHERE FOLIO IN (SELECT FOLIO FROM reportes WHERE COLONIA = :cp1)";
            $stmtCrimenes = $this->pdo->prepare($sqlCrimenes);
            $stmtCrimenes->execute(['cp1' => $codigoPostal]);

            // 2. Eliminar los reportes asociados a esta colonia
            $sqlReportes = "DELETE FROM reportes WHERE COLONIA = :cp2";
            $stmtReportes = $this->pdo->prepare($sqlReportes);
            $stmtReportes->execute(['cp2' => $codigoPostal]);

            // 3. Eliminar la colonia
            $sqlColonia = "DELETE FROM colonias WHERE CODIGO_POSTAL = :cp3";
            $stmtColonia = $this->pdo->prepare($sqlColonia);
            $stmtColonia->execute(['cp3' => $codigoPostal]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}