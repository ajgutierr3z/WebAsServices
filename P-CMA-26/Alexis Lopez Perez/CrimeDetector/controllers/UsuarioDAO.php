<?php    

    require_once __DIR__ . "/../models/Usuario.php";

    class UsuarioDAO{
        private PDO $pdo;

        public function __construct(PDO $pdo){
            $this->pdo = $pdo;
        }

        public function obtenerUsuarioPorCorreo(string $correo): ?Usuario{
            $stmt = $this->pdo->prepare("SELECT nombre, correo, password, foto_perfil, rol FROM usuarios WHERE correo = :correo LIMIT 1");

            $stmt->execute(['correo' => $correo]);

            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datos) {
                return null;
            }

            return new Usuario(
                correo: $datos['correo'],
                nombre: $datos['nombre'],
                pass: $datos['password'],
                foto_perfil: $datos['foto_perfil'],
                rol: $datos['rol']
            );
        }

        public function actualizarNombre(string $correo, string $nuevoNombre): bool {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET nombre = :nombre WHERE correo = :correo");
            return $stmt->execute([
                'nombre' => $nuevoNombre,
                'correo' => $correo
            ]);
        }

        public function actualizarPassword(string $correo, string $nuevoPasswordHash): bool {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET password = :password WHERE correo = :correo");
            return $stmt->execute([
                'password' => $nuevoPasswordHash,
                'correo' => $correo
            ]);
        }

        public function borrarUsuario($correo) {
            try {
                $this->pdo->beginTransaction();

                // 1. Asignar NULL a la columna USUARIO en todos los reportes creados por este usuario
                $sqlDesvincular = "UPDATE reportes SET USUARIO = NULL WHERE USUARIO = :correo";
                $stmtDesvincular = $this->pdo->prepare($sqlDesvincular);
                $stmtDesvincular->execute(['correo' => $correo]);

                // 2. Eliminar el usuario
                $sqlUsuario = "DELETE FROM usuarios WHERE CORREO = :correo";
                $stmtUsuario = $this->pdo->prepare($sqlUsuario);
                $stmtUsuario->execute(['correo' => $correo]);

                $this->pdo->commit();
                return true;

            } catch (PDOException $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        public function actualizarFotoPerfil(string $correo, string $rutaFoto): bool {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE correo = :correo");
            return $stmt->execute([
                'foto' => $rutaFoto,
                'correo' => $correo
            ]);
        }

        public function obtenerTodos(): array {
            $stmt = $this->pdo->prepare("SELECT nombre, correo, password, foto_perfil, rol FROM usuarios");
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $usuarios = [];
            foreach ($filas as $datos) {
                $usuarios[] = new Usuario(
                    correo: $datos['correo'],
                    nombre: $datos['nombre'],
                    pass: $datos['password'],
                    foto_perfil: $datos['foto_perfil'] ?? 'resources/img/default.png',
                    rol: $datos['rol']
                );
            }

            return $usuarios;
        }
        
        public function registrarUsuario(string $nombre, string $correo, string $passHash, string $rol): bool {
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (:nombre, :correo, :pass, :rol)");
            return $stmt->execute([
                'nombre' => $nombre,
                'correo' => $correo,
                'pass'   => $passHash,
                'rol'    => $rol
            ]);
        }
        
        public function actualizarRol(string $correo, string $rol): bool {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET rol = :rol WHERE correo = :correo");
            return $stmt->execute([
                'rol'    => $rol,
                'correo' => $correo
            ]);
        }
    }
    

