<?php

class TipoCrimenDAO {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerTodos(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM tipos_crimen ORDER BY CVE_TIPO_CRIMEN ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCve(int $cve): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM tipos_crimen WHERE CVE_TIPO_CRIMEN = :cve");
        $stmt->execute(['cve' => $cve]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function registrarTipoCrimen(string $nombre, int $gravedad): bool {
        $stmt = $this->pdo->prepare("INSERT INTO tipos_crimen (NOMBRE, GRAVEDAD) VALUES (:nombre, :gravedad)");
        return $stmt->execute([
            'nombre'   => $nombre,
            'gravedad' => $gravedad
        ]);
    }

    public function actualizarTipoCrimen(int $cve, string $nombre, int $gravedad): bool {
        $stmt = $this->pdo->prepare("UPDATE tipos_crimen SET NOMBRE = :nombre, GRAVEDAD = :gravedad WHERE CVE_TIPO_CRIMEN = :cve");
        return $stmt->execute([
            'nombre'   => $nombre,
            'gravedad' => $gravedad,
            'cve'      => $cve
        ]);
    }

    public function borrarTipoCrimen($cveTipoCrimen) {
        try {
            $this->pdo->beginTransaction();

            // 1. Eliminar primero los registros en 'crimenes' que usan esta clave
            $sqlCrimenes = "DELETE FROM crimenes WHERE CVE_TIPO_CRIMEN = :cve1";
            $stmtCrimenes = $this->pdo->prepare($sqlCrimenes);
            $stmtCrimenes->execute(['cve1' => $cveTipoCrimen]);

            // 2. Eliminar el tipo de crimen en 'tipos_crimen'
            $sqlTipo = "DELETE FROM tipos_crimen WHERE CVE_TIPO_CRIMEN = :cve2";
            $stmtTipo = $this->pdo->prepare($sqlTipo);
            $stmtTipo->execute(['cve2' => $cveTipoCrimen]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}