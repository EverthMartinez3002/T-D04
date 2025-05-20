# T-D04 - Pipeline DevOps Conversion Service

Este proyecto implementa dos microservicios de conversión de archivos (`node-converter` y `php-converter`) utilizando Node.js 18 y PHP-FPM 8.2.
Incorpora prácticas de DevOps como Git Flow, Conventional Commits, CI/CD con GitHub Actions, publicación de imágenes en Docker Hub y despliegue continuo mediante Docker Compose.

## 🌟 Características Principales

*   **Microservicios Independientes**:
    *   `node-converter`: Gestiona la recepción de archivos y la cola de tareas.
    *   `php-converter`: Realiza la conversión de archivos.
*   **Flujo de Trabajo DevOps Completo**:
    *   **Control de Versiones**: Git Flow.
    *   **Mensajes de Commit Estandarizados**: Conventional Commits.
    *   **Integración Continua (CI)**: GitHub Actions para linting, build, test y escaneo de seguridad.
    *   **Entrega Continua (CD)**: Publicación automática de imágenes Docker en Docker Hub y despliegue en un entorno de servidor.
*   **Contenerización**: Docker y Docker Compose para un entorno de desarrollo y despliegue consistente.
*   **Seguridad**: Escaneo de vulnerabilidades con Trivy y generación de SBOM (Software Bill of Materials).

## 📂 Estructura del Proyecto

```
conversion-service/
├── node-converter/            # Microservicio en Node.js
│   ├── src/
│   │   ├── app.js             # Lógica principal de la aplicación Express
│   │   └── index.js           # Punto de entrada del servidor Node.js
│   ├── uploads/               # Directorio para ficheros subidos temporalmente
│   ├── package.json
│   ├── package-lock.json
│   ├── Dockerfile
│   └── __tests__/
│       └── app.test.js        # Pruebas unitarias y de integración (Jest + Supertest)
│
├── php-converter/             # Microservicio en PHP
│   ├── src/
│   │   ├── app.php            # Lógica principal de la aplicación Slim
│   │   └── index.php          # Punto de entrada de PHP-FPM
│   ├── converted/             # Directorio para ficheros convertidos
│   ├── tests/
│   │   └── ConvertTest.php    # Pruebas unitarias (PHPUnit)
│   ├── composer.json
│   ├── composer.lock
│   ├── phpunit.xml
│   └── Dockerfile
│
└── docker-compose.yml         # Orquestación de los servicios para desarrollo y producción
```

## ⚙️ Prerrequisitos

Para ejecutar este proyecto, necesitarás:

*   [Docker](https://www.docker.com/get-started) & [Docker Compose](https://docs.docker.com/compose/install/)
*   [Node.js](https://nodejs.org/) (v18 o superior)
*   [PHP](https://www.php.net/downloads.php) (v8.2 o superior) & [Composer](https://getcomposer.org/download/)
*   [Git](https://git-scm.com/downloads)
*   (Opcional) [act](https://github.com/nektos/act) para probar los workflows de GitHub Actions localmente.

## 🚀 Desarrollo Local

1.  **Clonar el repositorio:**
    ```bash
    git clone <URL-DEL-REPOSITORIO>
    cd T-D04-Pipeline-DevOps
    ```

2.  **Configurar variables de entorno:**
    Copia el archivo `.env.example` a `.env` y ajusta las variables si es necesario.
    ```bash
    cp .env.example .env
    ```

3.  **Levantar los servicios con Docker Compose:**
    Esto construirá las imágenes (si no existen) y levantará los contenedores en segundo plano.
    ```bash
    docker-compose up --build -d
    ```

4.  **Verificar el estado de los contenedores:**
    ```bash
    docker-compose ps
    ```

## 🔧 Endpoints de la API

Ambos servicios exponen un endpoint `/health` para verificar su estado.

### Node-converter (Puerto `4000`)

*   **`GET /health`**: Verifica el estado del servicio.
    *   Respuesta (Ejemplo):
        ```json
        {
          "status": "ok",
          "timestamp": "2025-05-20T10:00:00.000Z"
        }
        ```

*   **`POST /convert`**: Inicia una nueva tarea de conversión.
    *   **Tipo de Contenido**: `multipart/form-data`
    *   **Campos del formulario**:
        *   `archivo`: El fichero a convertir (subida de archivo real).
        *   `formato`: El formato deseado para la conversión (ej. `pdf`, `txt`).
    *   **Respuesta HTTP `202` (Accepted)**:
        ```json
        {
          "status": "pendiente",
          "id": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
        }
        ```

*   **`GET /convert/:id`**: Consulta el estado de una tarea de conversión.
    *   **Parámetros de Ruta**:
        *   `id`: El UUID de la tarea.
    *   **Respuesta (Ejemplo)**:
        ```json
        {
          "status": "completado", // o "pendiente", "error"
          "id": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
          "resultUrl": "/converted/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.pdf" // si está completado
        }
        ```

### PHP-converter (Puerto `4001`)

*   **`GET /health`**: Verifica el estado del servicio.
    *   Respuesta (Ejemplo):
        ```json
        {
          "status": "ok",
          "timestamp": "2025-05-20T10:00:00.000Z"
        }
        ```

*   **`POST /convert`**: Procesa una tarea de conversión. (Este endpoint es llamado internamente por `node-converter`).
    *   **Tipo de Contenido**: `application/json`
    *   **Cuerpo de la Solicitud (Ejemplo)**:
        ```json
        {
          "id": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
          "input": {
            "filePath": "/uploads/tmp1234",
            "originalName": "documento.docx",
            "formato": "pdf"
          }
        }
        ```
    *   **Respuesta HTTP `200` (OK)**:
        ```json
        {
          "status": "completado",
          "id": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
          "resultUrl": "/converted/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.pdf"
        }
        ```

*   **`GET /converted/{file}`**: Descarga el fichero convertido.
    *   **Parámetros de Ruta**:
        *   `file`: El nombre del archivo convertido (generalmente `id-tarea.formato`).

## 🧪 Ejecución de Pruebas

### Node.js (`node-converter`)

```bash
cd conversion-service/node-converter
npm install
npm test
```

### PHP (`php-converter`)

```bash
cd conversion-service/php-converter
composer install
composer test
```

## 📋 Flujo de CI/CD con GitHub Actions

Este proyecto utiliza GitHub Actions para automatizar el proceso de CI/CD.

*   **Badges de Estado** (¡Añade estos a tu `README.md` reemplazando `tu-orga/tu-repo`!):
    ```markdown
    [![CI/CD Pipeline](https://github.com/tu-orga/tu-repo/actions/workflows/ci.yml/badge.svg)](https://github.com/tu-orga/tu-repo/actions/workflows/ci.yml)
    ```

*   **Linting de Commits**: Se utiliza [Conventional Commits](https://www.conventionalcommits.org/) para estandarizar los mensajes de commit, lo que facilita la generación de changelogs y el versionado semántico.

*   **Workflow `ci.yml`**:
    1.  **Build**: Construye las imágenes Docker para ambos microservicios usando Docker Buildx.
    2.  **Test**: Ejecuta las suites de pruebas (Jest/Supertest para Node.js, PHPUnit para PHP).
    3.  **Scan**:
        *   Escanea las imágenes Docker en busca de vulnerabilidades con [Trivy](https://github.com/aquasecurity/trivy).
        *   Genera un SBOM (Software Bill of Materials) en formato SPDX-JSON utilizando [Anchore SBOM Action](https://github.com/anchore/sbom-action).

*   **Push a Docker Hub**: Las imágenes Docker se publican en Docker Hub cuando se crea un tag semántico (ej. `v1.0.0`, `v1.0.1`).

*   **Deploy**: El despliegue se activa automáticamente en la rama `main` (o la rama principal configurada). Se ejecuta en un runner auto-hospedado (self-hosted) que realiza un `docker-compose pull` y `docker-compose up -d`.

## 🚢 Publicación y Despliegue Manual

1.  **Crear un tag semántico y hacer push:**
    Sigue las convenciones de [Versionado Semántico](https://semver.org/).
    ```bash
    git tag v1.0.0
    git push origin v1.0.0
    ```
    Esto disparará el workflow de GitHub Actions que construye, prueba, escanea y publica las imágenes en Docker Hub.

2.  **Despliegue en el servidor (runner self-hosted):**
    El workflow de despliegue (generalmente activado por un push a `main` después de un merge o directamente) ejecutará los siguientes comandos en el servidor:
    ```bash
    # Comandos ejecutados por el runner en el servidor
    cd /ruta/al/proyecto/en/el/servidor
    docker-compose pull
    docker-compose up -d --remove-orphans
    ```

## 🔒 Seguridad

*   **Gestión de Secrets**: Las credenciales para Docker Hub (`REGISTRY_URL`, `REGISTRY_USERNAME`, `REGISTRY_PASSWORD`) se gestionan como secrets en la configuración del repositorio de GitHub.
*   **SBOM**: Se genera un SBOM para cada imagen, lo que ayuda a rastrear las dependencias y sus vulnerabilidades. El archivo se sube como artefacto en la ejecución del workflow.
*   **Escaneo de Vulnerabilidades**: Trivy analiza las imágenes Docker. Por defecto, el workflow está configurado para que `exit-code 0` (solo informa, no falla el pipeline por vulnerabilidades encontradas), pero esto se puede ajustar según las políticas de seguridad.

## 📖 Más Información y Recursos

*   [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
*   [Documentación de GitHub Actions](https://docs.github.com/en/actions)
*   [Documentación de Docker Compose](https://docs.docker.com/compose/)
*   [Trivy - Escáner de Vulnerabilidades](https://github.com/aquasecurity/trivy)
*   [Anchore SBOM Action](https://github.com/anchore/sbom-action)

---

*Nota: Recuerda ajustar las rutas, nombres de usuario/organización y cualquier otro valor específico de tu entorno en los comandos y configuraciones.*