<?php
class Usuario {
    public function __construct(        
        public string $correo,
        public string $nombre,        
        public string $pass,
        public string $foto_perfil,
        public string $rol
    ) {}
}