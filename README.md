<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title></title>
	<meta name="generator" content="LibreOffice 25.2.3.2 (Linux)"/>
	<meta name="created" content="00:00:00"/>
	<meta name="changed" content="2026-11-20T09:43:54.415696586"/>
	<style type="text/css">
		@page { size: 21.59cm 27.94cm; margin-left: 2cm; margin-right: 1cm; margin-top: 1cm; margin-bottom: 1cm }
		p { margin-bottom: 0.25cm; line-height: 115%; background: transparent }
		a:link { color: #000080; text-decoration: underline }
		a:visited { color: #800000; text-decoration: underline }
	</style>
</head>
<body lang="es-MX" link="#000080" vlink="#800000" dir="ltr"><p align="center" style="line-height: 100%; margin-bottom: 0cm">
# WebAsServices [![GitHub last
commit](https://img.shields.io/github/last-commit/ajgutierr3z/WebAsServices)](https://github.com/ajgutierr3z/WebAsServices/commits/main)
[![GitHub pull
requests](https://img.shields.io/github/issues-pr/ajgutierr3z/WebAsServices)](https://github.com/ajgutierr3z/WebAsServices/pulls)
[![CI
Status](https://github.com/ajgutierr3z/WebAsServices/actions/workflows/revisar.yml/badge.svg)](https://github.com/ajgutierr3z/WebAsServices/actions)
[![GitHub
stars](https://img.shields.io/github/stars/ajgutierr3z/WebAsServices)](https://github.com/ajgutierr3z/WebAsServices/stargazers)
[![License:
MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
</p>
<p style="line-height: 100%; margin-bottom: 0cm">Repositorio oficial
de la materia **Aplicaciones Web Orientada a Servicios**. Aquí
encontrarás todos los códigos de ejemplo que usaremos en clase y la
base para que realices tus prácticas. --- ## 📑 Tabla de
Contenidos - [📚 Estructura del
Repositorio](#-estructura-del-repositorio) - [🚀 Flujo de Trabajo
para Alumnos (¿cómo entregar tu
práctica?)](#-flujo-de-trabajo-para-alumnos-c%C3%B3mo-entregar-tu-pr%C3%A1ctica)
- [🤖 Automatización CI/CD](#-automatización-cicd) - [📊
Estadísticas de Entregas](#-estadísticas-de-entregas) - [📖
Licencia y Contacto](#licencia-y-contacto) --- ## 📚 Estructura del
Repositorio &gt; ⚠️ **Para alumnos:** Solo deben modificar los
archivos dentro de la carpeta indicada para cada práctica. El resto
del repositorio es material de ejemplo del profesor. | Carpeta |
Propósito | ¿Los alumnos modifican? |
|---------|-----------|------------------------| | `APIRest/` |
Ejemplo base de API &quot;Hola Mundo&quot; y plantilla para la
primera práctica. | ✅ **Sí** (aquí harán sus entregas) | |
`demo_soa/` | Demostraciones de arquitectura orientada a servicios. |
❌ No (solo lectura) | | *(Futuras carpetas)* | *Se irán explicando
en clase según se agreguen.* | *Dependerá del ejercicio* | --- ##
🚀 Flujo de Trabajo para Alumnos (Cómo entregar tu práctica)
Seguirás el flujo estándar de colaboración en GitHub: **Fork →
Clonar → Modificar → Pull Request**. ### Paso 1: Haz un Fork 1.
Asegúrate de tener una [cuenta de GitHub
gratuita](https://github.com/join). 2. Ve al repositorio original:
`https://github.com/ajgutierr3z/WebAsServices` 3. Haz clic en el
botón **Fork** (esquina superior derecha). ### Paso 2: Clona tu Fork
```bash git clone https://github.com/TU_USUARIO/WebAsServices.git cd
WebAsServices ``` ### Paso 3: Modifica (solo la carpeta que se
indicada) Ejemplo para la primera práctica: crea un archivo dentro
de APIRest/ con tu nombre: ```php # Si usas PHP echo &quot;&quot; &gt;
APIRest/tu_nombre.php ``` o python ```python # Si usas Python echo
&quot;print('Hola desde [Tu Nombre]')&quot; &gt; APIRest/tu_nombre.py
``` ### Paso 4: Sube los cambios ```bash git add . git commit -m
&quot;Práctica: [Tu Nombre Completo]&quot; git push origin main ```
### Paso 5: Crea el Pull Request (PR) - Ve a tu fork en GitHub. - Haz
clic en &quot;Compare &amp; pull request&quot; (botón amarillo). -
Verifica que diga: ```bash base repository: ajgutierr3z/WebAsServices
← head repository: TU_USUARIO/WebAsServices ``` - Escribe un título
(ej. &quot;Entrega de [Tu Nombre]&quot;) y haz clic en &quot;Create
pull request&quot;. - ✅ Listo. Tu entrega queda registrada. No es
necesario que yo haga merge. &gt; 📖 Para más detalles, consulta
la guía completa en el [blog de
Frexus](https://www.frexus.dev/post/github-y-pull-requests/). --- ##
🤖 Automatización CI/CD Este repositorio utiliza **GitHub
Actions** para automatizar la revisión de entregas. ### ¿Qué
valida automáticamente? | Validación | Estado | Descripción |
|------------|--------|-------------| | Protección `demo_soa/` | ✅
Activo | Bloquea modificaciones a ejemplos del profesor | |
Protección `.github/` | ✅ Activo | Protege la configuración del
CI | | Sintaxis PHP/Python/JS | ✅ Activo | Verifica que el código
sea válido | | Tamaño de archivos | ✅ Activo | Limita archivos a
100KB | | Extensiones permitidas | ✅ Activo | Solo .php, .py, .js,
.html, .css | ### 📊 Resultados en cada PR Cuando un alumno crea un
Pull Request, el CI: 1. **Revisa automáticamente** la estructura 2.
**Deja un comentario** con los resultados 3. **Agrega labels** como
`auto-validado` 4. **No cierra el PR** (el profesor lo hace
manualmente) ![Estado del
CI](https://github.com/ajgutierr3z/WebAsServices/actions/workflows/revisar.yml/badge.svg)
--- ## 📊 Estadísticas de Entregas ### 📈 Métricas del
repositorio | Métrica | Valor | |---------|-------| | Total de PRs |
![GitHub pull
requests](https://img.shields.io/github/issues-pr/ajgutierr3z/WebAsServices)
| | Entregas validadas | ![PRs
Cerrados](https://img.shields.io/github/issues-pr-closed/ajgutierr3z/WebAsServices?label=&amp;color=success)
| | Alumnos activos | ![GitHub
forks](https://img.shields.io/github/forks/ajgutierr3z/WebAsServices?label=&amp;color=informational)
| | Cobertura de prácticas | Todas| ### 🏷️ Estado de las
entregas Usamos labels para clasificar los PRs: - 🟢
`entrega-completa` - Entregas que cumplen todos los requisitos - 🟡
`needs-feedback` - Esperando revisión del profesor - 🔵
`feedback-dado` - Ya se proporcionó retroalimentación - 🟣
`ejemplo-destacado` - Casos destacados ### 📊 Dashboard visual
Puedes ver el tablero de proyectos aquí: [![Project
Status](https://img.shields.io/badge/Project-Kanban-blue)](https://github.com/users/ajgutierr3z/projects/2)
--- ### ¿Qué esperar después de tu PR? | Acción del profesor |
¿Qué significa para ti?|
|---------------------|------------------------| |Comentario en el PR
| Revisé tu código, puede tener feedback para mejorar.| |PR cerrado
(sin merge)| Entregaste correctamente, la práctica queda
registrada.| |PR cerrado (con merge)| Tu código fue incorporado al
repositorio principal (casos excepcionales).| --- ### ¿Por qué usar
Pull Requests y no enviar archivos? - Aprendes el flujo real que se
usa en todas las empresas de software. - Recibes feedback en el
código línea por línea. - Todo queda trazable para tu portafolio.
### Licencia y contacto - Profesor: Alfredo de Jesús Gutiérrez
Gómez - Blog: [frexus.dev](www.frexus.dev) - Propósito:
Exclusivamente educativo. - Licencia: MIT (puedes usar el código,
pero cita la fuente). 
</p>
<p style="line-height: 100%; margin-bottom: 0cm"><br/>

</p>
<p style="line-height: 100%; margin-bottom: 0cm"><br/>

</p>
<p style="line-height: 100%; margin-bottom: 0cm">---</p>
<p style="line-height: 100%; margin-bottom: 0cm">### Práctica de
Pull Request</p>
<p style="line-height: 100%; margin-bottom: 0cm">**Alumno:** Emmanuel
González de los Santos</p>
<p style="line-height: 100%; margin-bottom: 0cm">**Descripción:** He
agregado esta sección como parte de mi práctica de GitHub para
aprender a hacer un Pull Request.</p>
</body>
</html>