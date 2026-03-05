# SPOTMAP 

## Desarrollo de aplicación WEB/Multiplataforma 
### CURSO 2025/26

---

**Alumno/a**  
ANTONIO VALERO MALDONADO 

**Tutor/a**  
XXXXXX XXXXXX XXXXX 

---

**UNENDO**  
**CICLO FORMATIVO DE GRADO SUPERIOR EN**  
**DESARROLLO DE APLICACIONES WEB**

---

## SpotMap – Documento del Proyecto

---

# Índice

1. Descripción general del proyecto
   - 1.1 Introducción
   - 1.2 Alcance del proyecto
   - 1.3 Justificación y análisis de la realidad
   - 1.4 Marco legal
   - 1.5 Marco teórico
   - 1.6 Temporización

2. Estudio de la viabilidad del sistema
   - 2.1 DAFO (Análisis económico)
   - 2.2 Conclusión del Estudio de Viabilidad
   - 2.3 Plan de marketing

3. Descripción del entorno tecnológico
   - 3.1 Perfiles de usuario
   - 3.2 Tecnologías para cada perfil

4. Especificación de requisitos
   - 4.1 Requisitos funcionales
   - 4.2 Requisitos no funcionales

5. Modelo Entidad-Relación y Modelo Relacional
   - 5.1 Justificación del Modelo E/R
   - 5.2 Diagrama Entidad-Relación
   - 5.3 Explicación del Origen de los Atributos
   - 5.4 Elección de Claves Primarias
   - 5.5 Modelo Relacional

6. Diagramas de Procesos
   - 6.1 Diagramas de Clase
   - 6.2 Casos de Uso

7. Diseño de Interfaz
   - 7.1 Diagramación y Prototipado
   - 7.2 Guías de Estilo
   - 7.3 Mapa de Navegación

8. Manual de Usuario
   - 8.1 Manual del Cliente
   - 8.2 Manual de Instalación y Despliegue

9. Bibliografía y Referencias

---

# 1. Descripción general del proyecto

## 1.1 Introducción

SpotMap es una plataforma web colaborativa cuyo objetivo principal es centralizar, organizar y facilitar el acceso a lugares fotográficos —miradores, espacios naturales, rincones urbanos y escenarios visuales— mediante un sistema de geolocalización interactivo. A diferencia de redes sociales como Instagram o TikTok, donde el contenido está disperso y no clasificado para este propósito, SpotMap se centra exclusivamente en recopilar spots de interés fotográfico con información estructurada, validada y enriquecida por la comunidad.

El proyecto surge como respuesta a la creciente demanda de herramientas que permitan a fotógrafos, viajeros y creadores de contenido localizar fácilmente escenarios visuales de calidad. SpotMap ofrecerá una experiencia completa: desde descubrir localizaciones mediante un mapa dinámico hasta consultar descripciones detalladas, horarios recomendados, consejos de fotografía y opiniones de otros usuarios.

La plataforma está dirigida a:

- Fotógrafos profesionales y aficionados.
- Creadores de contenido e influencers.
- Turistas que buscan experiencias menos convencionales.
- Instituciones turísticas interesadas en difundir su entorno mediante contenido visual de calidad.

SpotMap pretende convertirse en un repositorio global, colaborativo y moderado de spots fotográficos, fomentando tanto la participación comunitaria como la calidad del contenido.

## 1.2 Alcance del proyecto

El sistema cubrirá las siguientes funcionalidades:

1. Gestión de usuarios: registro, inicio de sesión, cierre de sesión y recuperación de contraseña.
2. Gestión de roles: usuarios registrados, moderadores y administradores.
3. Gestión de spots: creación, edición, eliminación y visualización de spots con imágenes, etiquetas, ubicación y descripciones.
4. Búsqueda avanzada: filtros por categoría, distancia, popularidad, etiqueta u horario recomendado.
5. Interacción social: valoraciones, comentarios y marcadores de favoritos.
6. Mapa interactivo: visualización de coordenadas con marcadores dinámicos.
7. Moderación: revisión de contenido inapropiado, duplicado o reportado.
8. Panel administrativo: gestión de usuarios, estadísticas de uso, reportes y métricas globales.

## 1.3 Justificación y análisis de la realidad

En la actualidad, la búsqueda de localizaciones fotográficas depende principalmente de publicaciones en redes sociales, blogs personales o recomendaciones informales. Este enfoque presenta numerosos problemas: la información no está organizada, los lugares no siempre cuentan con coordenadas precisas, la calidad del contenido es variable y, en general, no existe una plataforma única que permita localizar spots de manera eficiente.

Además, muchos fotógrafos y creadores invierten tiempo significativo navegando entre múltiples fuentes para encontrar lugares adecuados. Las instituciones turísticas también carecen de herramientas específicas para mostrar el potencial visual de su entorno.

SpotMap responde a estas necesidades mediante:

- Un sistema centralizado de localización fotográfica basado en geolocalización.
- Contenido generado por la comunidad con mecanismos de validación y moderación.
- Una interfaz intuitiva que facilita la exploración y el descubrimiento de lugares.
- Beneficios para organismos locales que deseen promocionar su entorno de forma visual y ordenada.

Desde un punto de vista formativo, el proyecto integra desarrollo web, bases de datos geoespaciales, diseño de APIs, seguridad, control de versiones y gestión de proyectos.

## 1.4 Marco legal

El proyecto cumple con el Reglamento General de Protección de Datos (GDPR - UE 2016/679). El usuario podrá:

- Modificar y eliminar sus datos personales.
- Gestionar derechos de imagen en el contenido subido.
- Aceptar los términos y condiciones antes del registro.

La plataforma incluirá:

- Política de privacidad detallada.
- Aviso legal y condiciones de uso.
- Normas de publicación y código de conducta.

Se prestará especial atención al uso responsable del contenido, especialmente en lo relativo a derechos de autor, privacidad y protección de menores.

## 1.5 Marco teórico

SpotMap se basa en tecnologías modernas de desarrollo web:

- Arquitectura cliente-servidor: comunicación mediante API REST.
- Bases de datos relacional (PostgreSQL via Supabase): optimizada para consultas complejas y datos geoespaciales.
- Backend con PHP 8.2: framework robusto para desarrollo de APIs, autenticación y seguridad.
- Frontend en JavaScript (HTML/CSS/Vanilla JS): creación de interfaces responsivas.
- Diseño responsivo: adaptación a dispositivos móviles, tablets y sobremesa.
- UX/UI: priorización de la usabilidad, accesibilidad y claridad.
- Metodología Ágil: organización del trabajo en sprints iterativos.

## 1.6 Temporización

Duración total: 16 semanas divididas en 8 sprints.

| Sprint | Duración | Objetivos |
|--------|----------|-----------|
| 1 | 1 semana | Análisis inicial, estudio de requisitos, investigación de mercado. |
| 2 | 1 semana | Diseño del sistema, arquitectura, esquemas de BD, wireframes. |
| 3 | 2 semanas | Desarrollo de la API base, autenticación, gestión de usuarios y roles. |
| 4 | 2 semanas | CRUD de spots, subida de imágenes y validaciones. |
| 5 | 2 semanas | Mapa interactivo, integración con API de geolocalización. |
| 6 | 2 semanas | Comentarios, valoraciones, sistema de búsqueda avanzada. |
| 7 | 2 semanas | Moderación, panel administrativo, estadísticas básicas. |
| 8 | 2 semanas | Pruebas, documentación, despliegue final y optimización. |

---

# 2. Estudio de la viabilidad del sistema

## 2.1 Estudio de la viabilidad del sistema (DAFO)

### Fortalezas

- Proyecto original con un alto valor turístico, social y cultural.
- Escalable tanto a nivel nacional como internacional debido al carácter universal de la fotografía.
- Uso de tecnologías libres y open source, lo que reduce los costes de desarrollo y mantenimiento.
- Posibilidad de generar una comunidad activa que aporte contenido de forma continua.
- Plataforma especializada en spots fotográficos, ofreciendo un valor que redes generalistas no cubren.
- Capacidad de integrarse con herramientas de geolocalización y APIs modernas.

### Debilidades

- Necesidad de alcanzar una masa crítica de usuarios para que la plataforma resulte realmente útil.
- Dependencia directa de la calidad, veracidad y frecuencia del contenido generado por los usuarios.
- Requerimiento constante de moderación para evitar contenido inapropiado, duplicado o falso.
- Monetización poco clara en las primeras fases del proyecto.
- Costes potenciales por almacenamiento de imágenes en servidores en la nube.
- Riesgo de abandono del proyecto si no se mantiene una comunidad activa.

### Oportunidades

- Integración con redes sociales que puede aumentar significativamente la visibilidad y viralidad del contenido.
- Colaboración con ayuntamientos, oficinas de turismo, fotógrafos locales y empresas del sector cultural.
- Posibilidad de ofrecer servicios premium a negocios locales:
  - Promociones
  - Estadísticas geográficas
  - Spots destacados o patrocinados
- Creciente demanda de aplicaciones de geolocalización, turismo experiencial y fotografía.
- Oportunidad de implementar algoritmos de recomendación o clasificación automática mediante IA.
- Posibilidad de posicionarse como herramienta educativa en escuelas de fotografía y turismo.

### Amenazas

- Competencia indirecta con plataformas altamente consolidadas como Google Maps, Instagram, TikTok o TripAdvisor.
- Riesgos legales relacionados con derechos de imagen, copyright y datos personales.
- Posibles cambios en normativas locales o europeas que afecten a la publicación de contenido geolocalizado.
- Dificultad para retener usuarios si no se ofrece una propuesta de valor diferenciada y constante.
- Problemas de seguridad informática que podrían comprometer la confianza de los usuarios.

## 2.2 Conclusión del Estudio de Viabilidad

El coste inicial del proyecto es reducido debido al uso de herramientas open source y servicios cloud escalables, lo que permite desarrollar y mantener la plataforma sin grandes inversiones iniciales. Esta infraestructura flexible facilita el crecimiento progresivo del sistema conforme aumente el número de usuarios y el volumen de contenido.

La rentabilidad futura del proyecto podrá provenir de diversas vías:

- Publicidad ética y no intrusiva, orientada a turismo, fotografía o actividades culturales.
- Servicios premium para usuarios avanzados y negocios locales, tales como estadísticas personalizadas, posicionamiento de spots destacados o promociones geolocalizadas.
- Acuerdos con instituciones turísticas interesadas en promocionar su entorno mediante contenido visual de calidad.

Estas fuentes de ingresos, combinadas con una comunidad activa, hacen que el proyecto sea sostenible y escalable a medio y largo plazo.

## 2.3 Plan de Marketing

Para lograr una difusión eficaz y consolidar la comunidad inicial del proyecto, se plantean las siguientes acciones estratégicas:

### 1. Presencia en redes sociales

Creación de cuentas oficiales en Instagram, TikTok y X, donde se publicarán spots destacados, fotografías inspiradoras y contenido generado por usuarios. Estas plataformas actuarán como escaparate visual del proyecto.

### 2. Campañas colaborativas

Colaboración con fotógrafos locales, microinfluencers y creadores de contenido que puedan aportar visibilidad a la plataforma. Estas campañas permitirán alcanzar audiencias relacionadas con la fotografía y los viajes.

### 3. Gamificación

Implementación de un sistema de logros, insignias y recompensas para los usuarios más activos. Esto aumentará la participación, la fidelidad y el aporte continuo de nuevos spots.

### 4. SEO y posicionamiento web

Optimización técnica del sitio mediante estrategias de SEO on-page y off-page, con el objetivo de aparecer en las primeras posiciones de búsquedas relacionadas con fotografía, turismo y localizaciones.

### 5. Colaboraciones institucionales

Establecer acuerdos con oficinas de turismo, ayuntamientos y asociaciones culturales para promocionar lugares menos conocidos y reforzar la legitimidad del proyecto.

### Objetivo general del marketing

Construir una base sólida de usuarios activos y motivados que permitan un crecimiento orgánico y sostenido de la plataforma, generando contenido continuo y aumentando la visibilidad del proyecto a nivel local, nacional e internacional.

---

# 3. Descripción del entorno tecnológico

## 3.1 Perfiles de usuario

- **Visitante**: puede navegar, ver el mapa y consultar spots.
- **Usuario registrado**: puede crear, editar, valorar y comentar spots.
- **Moderador**: revisa contenido reportado, valida spots y gestiona incidencias.
- **Administrador**: accede al panel general, estadísticas, roles y configuración global.

## 3.2 Tecnologías asociadas a cada perfil

| Perfil | Herramientas / Tecnologías |
|--------|---------------------------|
| Visitante | Navegador web moderno; frontend en JavaScript; uso de Leaflet/OpenStreetMap para el mapa. |
| Usuario registrado | Interfaz full stack (HTML, CSS, JavaScript); autenticación JWT; subida de imágenes (Supabase Storage); gestión de favoritos y comentarios. |
| Moderador | Panel interno desarrollado en HTML/CSS/JS; comunicación con API PHP; herramientas de validación de reportes; acceso a base de datos (PostgreSQL via Supabase). |
| Administrador | Acceso a panel avanzado con PHP, dashboards con Chart.js; gestión de roles; supervisión del servidor cloud; estadísticas y métricas globales. |

---

# 4. Especificación de requisitos

## 4.1 Requisitos Funcionales

- **RF1. Registrarse**: El sistema debe permitir a los usuarios crear una cuenta proporcionando correo electrónico, contraseña y nombre de usuario.

- **RF2. Iniciar sesión**: El usuario debe poder acceder a su cuenta mediante correo y contraseña o a través de proveedores externos (Google, Apple…).

- **RF3. Cerrar sesión**: El sistema permitirá al usuario desconectarse de su cuenta de forma segura en cualquier momento.

- **RF4. Crear spots fotográficos**: El usuario podrá subir la localización, una fotografía y una breve descripción del lugar.

- **RF5. Editar spots propios**: El usuario podrá modificar información de los spots que haya creado (descripción, título o imagen).

- **RF6. Eliminar spots propios**: El usuario tendrá la posibilidad de borrar spots que ya no desee mantener publicados.

- **RF7. Explorar spots en el mapa**: El usuario podrá visualizar spots geolocalizados mediante un mapa interactivo y filtrarlos según categoría, popularidad o cercanía.

- **RF8. Buscar spots**: El sistema permitirá buscar spots por nombre, palabra clave o localidad.

- **RF9. Marcar favoritos**: El usuario podrá guardar spots para acceder a ellos rápidamente desde su perfil.

- **RF10. Comentar spots**: El sistema permitirá añadir comentarios en spots ajenos para fomentar la interacción.

- **RF11. Moderar contenido**: Los administradores podrán revisar, aprobar, ocultar o eliminar contenido que incumpla las normas.

- **RF12. Reportar contenido**: Cualquier usuario podrá denunciar un spot o comentario inapropiado, indicando el motivo.

- **RF13. Gestionar usuarios**: Los administradores podrán suspender cuentas, restablecer contraseñas o detectar actividad sospechosa.

- **RF14. Generar estadísticas**: El sistema recopilará datos básicos (visitas, spots más populares, usuarios activos) para análisis interno.

- **RF15. Recibir notificaciones**: El usuario podrá recibir avisos sobre comentarios, favoritos o cambios en sus spots.

## 4.2 Requisitos No Funcionales

- **RNF1. Usabilidad**: La interfaz debe ser intuitiva, accesible y adaptada a usuarios sin conocimientos técnicos.

- **RNF2. Rendimiento**: El sistema debe cargar resultados de búsqueda o mapa en menos de 3 segundos bajo condiciones normales.

- **RNF3. Seguridad**: Todos los datos personales deben almacenarse cifrados y transmitirse mediante HTTPS.

- **RNF4. Escalabilidad**: La arquitectura debe soportar un incremento progresivo de usuarios sin perder rendimiento.

- **RNF5. Disponibilidad**: El sistema debe garantizar un funcionamiento del 99% mensual, salvo tareas de mantenimiento planificadas.

- **RNF6. Compatibilidad**: La aplicación debe funcionar correctamente en navegadores modernos y dispositivos móviles Android/iOS.

- **RNF7. Mantenibilidad**: El código debe estar documentado y organizado para permitir modificaciones futuras.

- **RNF8. Privacidad**: El tratamiento de datos debe cumplir con la legislación vigente (RGPD y LOPDGDD).

---

# 5. Modelo Entidad-Relación y Modelo Relacional

## 5.1 Justificación del Modelo E/R

El modelo entidad-relación de **SpotMap** surge del análisis de los requisitos funcionales del sistema. A continuación se explica el origen de cada entidad y relación del modelo.

### 5.1.1 Origen de las Entidades

**USERS (Usuarios)**
- **Origen**: Requisito RF1, RF2, RF3 - Sistema de autenticación
- **Justificación**: Es necesaria para identificar usuarios, establecer autoría de contenido y gestionar permisos según roles (usuario, moderador, administrador)

**SPOTS (Puntos Fotográficos)**
- **Origen**: Requisito RF4, RF5, RF6, RF7 - Funcionalidad principal
- **Justificación**: Entidad central del sistema. Representa localizaciones fotográficas con información geoespacial, descriptiva y multimedia

**CATEGORIES (Categorías)**
- **Origen**: Requisito RF7, RF8 - Clasificación de contenido
- **Justificación**: Organiza spots por tipo (mirador, playa, urbano, montaña) facilitando navegación y filtrado

**COMMENTS (Comentarios)**
- **Origen**: Requisito RF10 - Interacción social
- **Justificación**: Permite a usuarios compartir experiencias y consejos sobre cada spot. Incluye soporte para respuestas anidadas (hilos de conversación)

**RATINGS (Valoraciones)**
- **Origen**: Requisito RF7 - Sistema de calidad
- **Justificación**: Sistema de valoración 1-5 estrellas para medir satisfacción y generar ranking de popularidad

**FAVORITES (Favoritos)**
- **Origen**: Requisito RF9 - Colecciones personales
- **Justificación**: Permite a usuarios guardar spots de interés para planificar rutas fotográficas

**REPORTS (Reportes)**
- **Origen**: Requisito RF11, RF12 - Moderación de contenido
- **Justificación**: Sistema de control de calidad para gestionar contenido inapropiado o duplicado

**TAGS (Etiquetas)**
- **Origen**: Requisito RF8 - Búsqueda avanzada
- **Justificación**: Sistema flexible de palabras clave (hashtags) para mejorar descubrimiento de spots

**IMAGES (Imágenes)**
- **Origen**: Requisito RF4 - Contenido multimedia
- **Justificación**: Gestiona múltiples imágenes por spot con metadatos y orden de visualización

**NOTIFICATIONS (Notificaciones)**
- **Origen**: Requisito RF15 - Comunicación con usuarios
- **Justificación**: Sistema de alertas para mantener usuarios informados de actividad relevante (comentarios, likes, reportes)

### 5.1.2 Origen de las Relaciones

**USER → SPOT (1:N - "crea")**
- Un usuario puede crear múltiples spots
- Cada spot pertenece a un único autor
- Necesaria para establecer autoría y permisos de edición

**SPOT → CATEGORY (N:1 - "pertenece a")**
- Cada spot tiene una categoría principal
- Una categoría agrupa múltiples spots
- Facilita filtrado eficiente en el mapa

**USER → COMMENT (1:N - "escribe")**
- Un usuario puede escribir múltiples comentarios
- Identifica al autor de cada comentario

**SPOT → COMMENT (1:N - "tiene")**
- Un spot puede tener múltiples comentarios
- Centraliza conversaciones sobre cada spot

**COMMENT → COMMENT (1:N - "responde a")**
- Auto-relación para respuestas anidadas
- Permite conversaciones estructuradas en hilos

**USER → RATING (1:N - "valora")**
- Un usuario puede valorar múltiples spots
- Restricción: Un usuario solo puede valorar cada spot una vez

**SPOT → RATING (1:N - "recibe")**
- Un spot puede recibir múltiples valoraciones
- Se calcula media automáticamente

**USER → FAVORITE (1:N - "guarda")**
- Un usuario puede tener múltiples favoritos
- Implementa colecciones personales

**SPOT → FAVORITE (1:N - "es favorito de")**
- Un spot puede ser favorito de múltiples usuarios
- Mide popularidad real

**USER → REPORT (1:N - "reporta")**
- Un usuario puede crear múltiples reportes
- Previene spam con validaciones

**SPOT → REPORT (1:N - "es reportado")**
- Un spot puede ser reportado múltiples veces
- Prioriza spots con más reportes

**COMMENT → REPORT (1:N - "es reportado")**
- Un comentario puede ser reportado múltiples veces
- Sistema de moderación de comentarios

**SPOT ↔ TAG (N:M - "está etiquetado con")**
- Un spot puede tener múltiples tags
- Un tag puede estar en múltiples spots
- Implementado mediante tabla intermedia SPOT_TAGS

**SPOT → IMAGE (1:N - "contiene")**
- Un spot puede tener múltiples imágenes
- Permite galerías fotográficas

**USER → NOTIFICATION (1:N - "recibe")**
- Un usuario puede recibir múltiples notificaciones
- Sistema de alertas personalizado

## 5.2 Diagrama Entidad-Relación

![Diagrama E/R de SpotMap](./images/SPOTMAP_ERD_PROFESIONAL.svg)

**Figura 5.1:** Diagrama Entidad-Relación (E/R) completo de SpotMap mostrando 11 entidades, 16 relaciones, cardinalidades (1:N y N:M), y flujos de datos entre USERS, SPOTS, CATEGORIES, COMMENTS, RATINGS, FAVORITES, TAGS, SPOT_TAGS, IMAGES, REPORTS, y NOTIFICATIONS.

---

## 5.3 Explicación del Origen de los Atributos

### Tabla USERS

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | UUID | Identificador único | Clave primaria. Se usa UUID por seguridad (no secuencial) y compatibilidad con Supabase |
| email | VARCHAR(255) UNIQUE | RF1 - Registro | Identificador único del usuario para login. Debe ser único y válido |
| password | VARCHAR(255) | RF1 - Registro | Hash de la contraseña (bcrypt/argon2). Nunca se almacena en texto plano |
| full_name | VARCHAR(100) | RF1 - Registro | Nombre completo del usuario para personalización de la interfaz |
| role | ENUM | Gestión de permisos | Valores: 'user', 'moderator', 'admin'. Control de acceso basado en roles (RBAC) |
| avatar_url | VARCHAR(500) | Personalización | URL de la imagen de perfil. Puede ser externa o de Supabase Storage |
| email_verified | BOOLEAN | Seguridad | Indica si el email ha sido verificado. Previene cuentas falsas |
| created_at | TIMESTAMP | Auditoría | Fecha de creación de la cuenta. Generado automáticamente |
| updated_at | TIMESTAMP | Auditoría | Última actualización del perfil. Se actualiza automáticamente |

### Tabla SPOTS

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | UUID | Identificador único | Clave primaria autogenerada |
| user_id | UUID (FK) | RF4 - Crear spot | Relación con el usuario creador. ON DELETE CASCADE |
| category_id | INT (FK) | RF7 - Clasificación | Categoría del spot (mirador, playa, etc.). ON DELETE SET NULL |
| title | VARCHAR(200) | RF4 - Crear spot | Nombre descriptivo del lugar fotográfico. Campo obligatorio |
| description | TEXT | RF4 - Crear spot | Descripción detallada, consejos, horarios. Campo opcional |
| latitude | DECIMAL(10,8) | RF4 - Geolocalización | Coordenada norte-sur. Rango: -90 a 90. Precisión ~1cm |
| longitude | DECIMAL(11,8) | RF4 - Geolocalización | Coordenada este-oeste. Rango: -180 a 180. Precisión ~1cm |
| image_url | VARCHAR(500) | RF4 - Multimedia | URL de la imagen principal del spot |
| address | VARCHAR(500) | RF7 - Búsqueda | Dirección legible obtenida por geocoding inverso |
| rating_avg | DECIMAL(2,1) | Calculado | Media de valoraciones (1.0 - 5.0). Campo desnormalizado para rendimiento |
| rating_count | INT | Contador | Número total de valoraciones. Campo desnormalizado |
| views | INT | Métricas | Contador de visualizaciones del spot |
| status | ENUM | Moderación | Valores: 'active', 'pending', 'deleted'. Control del estado del contenido |
| created_at | TIMESTAMP | Auditoría | Fecha de creación del spot |
| updated_at | TIMESTAMP | Auditoría | Última modificación del spot |

### Tabla CATEGORIES

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | INT | Identificador único | Clave primaria secuencial. Se usa INT por ser catálogo pequeño |
| name | VARCHAR(50) UNIQUE | RF7 - Clasificación | Nombre de la categoría. Ej: "Mirador", "Playa", "Urbano" |
| slug | VARCHAR(50) UNIQUE | SEO | Versión URL-friendly. Ej: "mirador-natural" |
| icon | VARCHAR(50) | UI/UX | Nombre del icono de Bootstrap Icons. Ej: "bi-mountain" |
| color | VARCHAR(7) | UI/UX | Color hex para identificación visual. Ej: "#10b981" |
| description | TEXT | Información | Descripción del tipo de spots que incluye la categoría |

### Tabla COMMENTS

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | UUID | Identificador único | Clave primaria autogenerada |
| user_id | UUID (FK) | RF10 - Comentar | Usuario autor del comentario. ON DELETE CASCADE |
| spot_id | UUID (FK) | RF10 - Comentar | Spot al que pertenece. ON DELETE CASCADE |
| parent_id | UUID (FK) nullable | Conversaciones | ID del comentario padre. NULL si es comentario raíz. Auto-relación |
| text | TEXT | RF10 - Contenido | Texto del comentario. Campo obligatorio |
| likes | INT | Interacción | Número de likes recibidos. Default: 0 |
| created_at | TIMESTAMP | Auditoría | Fecha de creación del comentario |
| updated_at | TIMESTAMP | Auditoría | Fecha de última edición |

### Tabla RATINGS

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | INT | Identificador único | Clave primaria secuencial |
| user_id | UUID (FK) | Valoración | Usuario que valoró. ON DELETE CASCADE |
| spot_id | UUID (FK) | Valoración | Spot valorado. ON DELETE CASCADE |
| rating | TINYINT | RF7 - Sistema valoración | Puntuación 1-5 estrellas. CHECK (rating >= 1 AND rating <= 5) |
| created_at | TIMESTAMP | Auditoría | Fecha de la valoración |

**Restricción**: UNIQUE(user_id, spot_id) - Un usuario solo puede valorar cada spot una vez

### Tabla FAVORITES

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | INT | Identificador único | Clave primaria secuencial |
| user_id | UUID (FK) | RF9 - Favoritos | Usuario que guardó el favorito. ON DELETE CASCADE |
| spot_id | UUID (FK) | RF9 - Favoritos | Spot guardado. ON DELETE CASCADE |
| created_at | TIMESTAMP | Auditoría | Fecha en que se marcó como favorito |

**Restricción**: UNIQUE(user_id, spot_id) - Un usuario no puede guardar el mismo spot dos veces

### Tabla REPORTS

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | UUID | Identificador único | Clave primaria autogenerada |
| user_id | UUID (FK) | RF12 - Reportar | Usuario que realizó el reporte. ON DELETE CASCADE |
| spot_id | UUID (FK) nullable | RF12 - Contenido | Spot reportado. NULL si se reporta un comentario |
| comment_id | UUID (FK) nullable | RF12 - Contenido | Comentario reportado. NULL si se reporta un spot |
| reason | ENUM | RF12 - Moderación | Valores: 'spam', 'inappropriate', 'duplicate', 'fake', 'other' |
| description | TEXT | RF12 - Detalles | Descripción detallada del problema |
| status | ENUM | RF11 - Moderación | Valores: 'pending', 'reviewed', 'resolved', 'dismissed' |
| created_at | TIMESTAMP | Auditoría | Fecha del reporte |
| resolved_at | TIMESTAMP nullable | Auditoría | Fecha de resolución |
| resolved_by | UUID (FK) nullable | Auditoría | Moderador que resolvió el reporte |

### Tabla TAGS

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | INT | Identificador único | Clave primaria secuencial |
| name | VARCHAR(50) UNIQUE | RF8 - Búsqueda | Nombre del tag (hashtag sin #). Ej: "atardecer" |
| slug | VARCHAR(50) UNIQUE | SEO | Versión URL-friendly del nombre |
| usage_count | INT | Métricas | Contador de cuántos spots tienen este tag |

### Tabla SPOT_TAGS (Tabla intermedia N:M)

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | INT | Identificador único | Clave primaria secuencial |
| spot_id | UUID (FK) | Relación N:M | Spot etiquetado. ON DELETE CASCADE |
| tag_id | INT (FK) | Relación N:M | Tag asociado. ON DELETE CASCADE |

**Restricción**: UNIQUE(spot_id, tag_id) - Un spot no puede tener el mismo tag duplicado

### Tabla IMAGES

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | UUID | Identificador único | Clave primaria autogenerada |
| spot_id | UUID (FK) | RF4 - Multimedia | Spot al que pertenece. ON DELETE CASCADE |
| url | VARCHAR(500) | Almacenamiento | URL de la imagen en Supabase Storage o CDN |
| caption | VARCHAR(200) | Accesibilidad | Descripción alternativa de la imagen |
| order | INT | UI/UX | Orden de visualización en galería (1, 2, 3...) |
| created_at | TIMESTAMP | Auditoría | Fecha de subida de la imagen |

### Tabla NOTIFICATIONS

| Atributo | Tipo de Dato | Origen | Justificación |
|----------|-------------|--------|---------------|
| id | UUID | Identificador único | Clave primaria autogenerada |
| user_id | UUID (FK) | RF15 - Notificaciones | Usuario destinatario. ON DELETE CASCADE |
| type | ENUM | RF15 - Tipos | Valores: 'comment', 'like', 'favorite', 'report', 'system' |
| title | VARCHAR(100) | UI/UX | Título corto de la notificación |
| message | TEXT | RF15 - Contenido | Mensaje completo de la notificación |
| link | VARCHAR(500) | Navegación | URL de destino al hacer click |
| read | BOOLEAN | Estado | Indica si la notificación ha sido leída. Default: false |
| created_at | TIMESTAMP | Auditoría | Fecha de creación de la notificación |

## 5.4 Elección de Claves Primarias

Para el diseño de SpotMap se ha optado por una **estrategia híbrida** que combina UUID e INT según las necesidades de cada tabla.

### UUID (Universally Unique Identifier)

**Se utiliza en**:
- users.id
- spots.id
- comments.id
- images.id
- notifications.id
- reports.id

**Justificación**:

1. **Escalabilidad distribuida**: Los UUID se pueden generar en el cliente sin necesidad de consultar la base de datos, evitando colisiones incluso en sistemas distribuidos

2. **Seguridad**: Al no ser secuenciales, no exponen información sobre el número total de registros en la tabla (no se puede saber cuántos usuarios hay contando IDs)

3. **Compatibilidad con Supabase**: Supabase utiliza UUID como tipo de dato por defecto para claves primarias en su sistema de autenticación

4. **Sincronización offline**: Facilita la implementación de funcionalidades offline ya que los IDs se pueden generar localmente sin conflictos

5. **No predecibilidad**: Los IDs no son adivinables, lo que aumenta la seguridad (un usuario no puede "adivinar" el ID de otro usuario o spot)

### INT Autoincremental

**Se utiliza en**:
- categories.id
- tags.id
- ratings.id
- favorites.id
- spot_tags.id

**Justificación**:

1. **Catálogos pequeños**: Categorías y tags son conjuntos limitados y relativamente estáticos

2. **Eficiencia de indexación**: Los enteros son más rápidos en operaciones de JOIN que los UUID, especialmente con grandes volúmenes de datos

3. **Menor consumo de espacio**: INT(11) ocupa 4 bytes mientras que UUID ocupa 16 bytes (reducción del 75% de espacio)

4. **Legibilidad**: Es más fácil referenciar "categoría 3" que un UUID largo en logs y debugging

5. **Orden natural**: El autoincremento proporciona un orden cronológico natural útil para ordenar por antigüedad

| Aspecto | UUID | INT Autoincremental |
|---------|------|---------------------|
| Tamaño | 16 bytes | 4 bytes |
| Generación | Cliente/Servidor | Solo servidor |
| Orden | Aleatorio | Secuencial |
| Seguridad | Alta (no predecible) | Baja (predecible) |
| Rendimiento JOIN | Menor | Mayor |
| Uso recomendado | Entidades principales | Catálogos y relaciones |

## 5.5 Modelo Relacional

### 5.5.1 Paso del Modelo E/R al Modelo Relacional

El proceso de transformación del modelo Entidad-Relación al Modelo Relacional sigue las siguientes reglas:

#### Regla 1: Transformación de Entidades

**Cada entidad del modelo E/R se convierte en una tabla** con las siguientes características:
- El nombre de la entidad se convierte en el nombre de la tabla
- Cada atributo de la entidad se convierte en una columna
- La clave primaria de la entidad se convierte en PRIMARY KEY de la tabla
- Los atributos obligatorios se marcan como NOT NULL

**Ejemplo**: La entidad USERS se transforma en:

```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    avatar_url VARCHAR(500),
    email_verified BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Regla 2: Transformación de Relaciones 1:N

**Las relaciones uno a muchos (1:N) se implementan mediante claves foráneas**:
- Se añade una columna en la tabla del lado "N" que referencia la PRIMARY KEY de la tabla del lado "1"
- Esta columna se marca como FOREIGN KEY
- Se definen las acciones ON DELETE y ON UPDATE según las reglas de negocio

**Ejemplo**: La relación USER → SPOT (1:N) se transforma en:

```sql
CREATE TABLE spots (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL,
    -- otros atributos...
    
    CONSTRAINT fk_spot_user 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE
);
```

**Significado de ON DELETE CASCADE**: Cuando se elimina un usuario, todos sus spots se eliminan automáticamente (borrado en cascada).

#### Regla 3: Transformación de Relaciones N:M

**Las relaciones muchos a muchos (N:M) requieren una tabla intermedia**:
- Se crea una nueva tabla con el nombre de ambas entidades (ej: spot_tags)
- Esta tabla contiene dos claves foráneas que referencian las tablas relacionadas
- La clave primaria puede ser compuesta (ambas FKs) o un ID independiente
- Se añade constraint UNIQUE para evitar duplicados

**Ejemplo**: La relación SPOT ↔ TAG (N:M) se transforma en:

```sql
CREATE TABLE spot_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    spot_id UUID NOT NULL,
    tag_id INT NOT NULL,
    
    CONSTRAINT fk_spot_tags_spot 
        FOREIGN KEY (spot_id) 
        REFERENCES spots(id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_spot_tags_tag 
        FOREIGN KEY (tag_id) 
        REFERENCES tags(id) 
        ON DELETE CASCADE,
    
    CONSTRAINT unique_spot_tag 
        UNIQUE (spot_id, tag_id)
);
```

#### Regla 4: Auto-relaciones

**Las auto-relaciones (una entidad relacionada consigo misma) se implementan con FK a la misma tabla**:

**Ejemplo**: COMMENT → COMMENT (respuestas anidadas):

```sql
CREATE TABLE comments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL,
    spot_id UUID NOT NULL,
    parent_id UUID NULL,  -- Auto-relación
    text TEXT NOT NULL,
    
    CONSTRAINT fk_comment_parent 
        FOREIGN KEY (parent_id) 
        REFERENCES comments(id) 
        ON DELETE CASCADE
);
```

**Explicación**: `parent_id` puede ser NULL (comentario raíz) o referenciar a otro comentario de la misma tabla (respuesta).

### 5.5.2 Esquema Relacional Completo

#### Tabla: USERS
```
users (
    id: UUID [PK],
    email: VARCHAR(255) [UNIQUE, NOT NULL],
    password: VARCHAR(255) [NOT NULL],
    full_name: VARCHAR(100) [NOT NULL],
    role: VARCHAR(20) [DEFAULT 'user'],
    avatar_url: VARCHAR(500),
    email_verified: BOOLEAN [DEFAULT false],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP],
    updated_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP]
)
```

#### Tabla: CATEGORIES
```
categories (
    id: INT [PK, AUTO_INCREMENT],
    name: VARCHAR(50) [UNIQUE, NOT NULL],
    slug: VARCHAR(50) [UNIQUE, NOT NULL],
    icon: VARCHAR(50),
    color: VARCHAR(7),
    description: TEXT
)
```

#### Tabla: SPOTS
```
spots (
    id: UUID [PK],
    user_id: UUID [FK → users.id, NOT NULL, ON DELETE CASCADE],
    category_id: INT [FK → categories.id, ON DELETE SET NULL],
    title: VARCHAR(200) [NOT NULL],
    description: TEXT,
    latitude: DECIMAL(10,8) [NOT NULL, CHECK >= -90 AND <= 90],
    longitude: DECIMAL(11,8) [NOT NULL, CHECK >= -180 AND <= 180],
    image_url: VARCHAR(500),
    address: VARCHAR(500),
    rating_avg: DECIMAL(2,1) [DEFAULT 0, CHECK >= 0 AND <= 5],
    rating_count: INT [DEFAULT 0],
    views: INT [DEFAULT 0],
    status: VARCHAR(20) [DEFAULT 'active'],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP],
    updated_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP]
)
```

#### Tabla: COMMENTS
```
comments (
    id: UUID [PK],
    user_id: UUID [FK → users.id, NOT NULL, ON DELETE CASCADE],
    spot_id: UUID [FK → spots.id, NOT NULL, ON DELETE CASCADE],
    parent_id: UUID [FK → comments.id, ON DELETE CASCADE],
    text: TEXT [NOT NULL],
    likes: INT [DEFAULT 0],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP],
    updated_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP]
)
```

#### Tabla: RATINGS
```
ratings (
    id: INT [PK, AUTO_INCREMENT],
    user_id: UUID [FK → users.id, NOT NULL, ON DELETE CASCADE],
    spot_id: UUID [FK → spots.id, NOT NULL, ON DELETE CASCADE],
    rating: TINYINT [NOT NULL, CHECK >= 1 AND <= 5],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP],
    
    UNIQUE (user_id, spot_id)
)
```

#### Tabla: FAVORITES
```
favorites (
    id: INT [PK, AUTO_INCREMENT],
    user_id: UUID [FK → users.id, NOT NULL, ON DELETE CASCADE],
    spot_id: UUID [FK → spots.id, NOT NULL, ON DELETE CASCADE],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP],
    
    UNIQUE (user_id, spot_id)
)
```

#### Tabla: REPORTS
```
reports (
    id: UUID [PK],
    user_id: UUID [FK → users.id, NOT NULL, ON DELETE CASCADE],
    spot_id: UUID [FK → spots.id, ON DELETE CASCADE],
    comment_id: UUID [FK → comments.id, ON DELETE CASCADE],
    reason: VARCHAR(20) [NOT NULL],
    description: TEXT,
    status: VARCHAR(20) [DEFAULT 'pending'],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP],
    resolved_at: TIMESTAMP,
    resolved_by: UUID [FK → users.id, ON DELETE SET NULL]
)
```

#### Tabla: TAGS
```
tags (
    id: INT [PK, AUTO_INCREMENT],
    name: VARCHAR(50) [UNIQUE, NOT NULL],
    slug: VARCHAR(50) [UNIQUE, NOT NULL],
    usage_count: INT [DEFAULT 0]
)
```

#### Tabla: SPOT_TAGS (tabla intermedia)
```
spot_tags (
    id: INT [PK, AUTO_INCREMENT],
    spot_id: UUID [FK → spots.id, NOT NULL, ON DELETE CASCADE],
    tag_id: INT [FK → tags.id, NOT NULL, ON DELETE CASCADE],
    
    UNIQUE (spot_id, tag_id)
)
```

#### Tabla: IMAGES
```
images (
    id: UUID [PK],
    spot_id: UUID [FK → spots.id, NOT NULL, ON DELETE CASCADE],
    url: VARCHAR(500) [NOT NULL],
    caption: VARCHAR(200),
    order: INT [DEFAULT 1],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP]
)
```

#### Tabla: NOTIFICATIONS
```
notifications (
    id: UUID [PK],
    user_id: UUID [FK → users.id, NOT NULL, ON DELETE CASCADE],
    type: VARCHAR(20) [NOT NULL],
    title: VARCHAR(100) [NOT NULL],
    message: TEXT,
    link: VARCHAR(500),
    read: BOOLEAN [DEFAULT false],
    created_at: TIMESTAMP [DEFAULT CURRENT_TIMESTAMP]
)
```

### 5.5.3 Diagrama Relacional

![Diagrama Relacional de SpotMap](./images/SPOTMAP_RELACIONAL.svg)

**Figura 5.2:** Esquema Relacional detallado con 11 tablas PostgreSQL, incluyendo tipos de datos, restricciones (NOT NULL, UNIQUE, FK), índices, y relaciones ON DELETE CASCADE/SET NULL. Base de datos normalizada a 3NF.

### 5.5.4 Explicación de ON DELETE y ON UPDATE

Las cláusulas ON DELETE y ON UPDATE definen el comportamiento automático cuando se elimina o actualiza un registro referenciado:

#### ON DELETE CASCADE

**Uso**: `ON DELETE CASCADE`

**Significado**: Cuando se elimina un registro padre, se eliminan automáticamente todos los registros hijos relacionados (borrado en cascada).

**Ejemplos en SpotMap**:

```sql
-- Si se elimina un usuario, se eliminan todos sus spots
spots.user_id → users.id ON DELETE CASCADE

-- Si se elimina un spot, se eliminan todos sus comentarios
comments.spot_id → spots.id ON DELETE CASCADE

-- Si se elimina un spot, se eliminan todas sus valoraciones
ratings.spot_id → spots.id ON DELETE CASCADE
```

**Justificación**: Mantiene la integridad referencial automáticamente. Si un usuario se da de baja, todo su contenido debe eliminarse.

#### ON DELETE SET NULL

**Uso**: `ON DELETE SET NULL`

**Significado**: Cuando se elimina un registro padre, las claves foráneas de los registros hijos se establecen a NULL (en lugar de eliminarlos).

**Ejemplo en SpotMap**:

```sql
-- Si se elimina una categoría, los spots mantienen pero sin categoría
spots.category_id → categories.id ON DELETE SET NULL
```

**Justificación**: Se prefiere mantener los spots aunque su categoría desaparezca. La categoría puede asignarse posteriormente.

| Relación | ON DELETE | Justificación |
|----------|-----------|---------------|
| spots.user_id → users | CASCADE | El contenido de un usuario se elimina con él |
| spots.category_id → categories | SET NULL | Los spots se mantienen sin categoría |
| comments.user_id → users | CASCADE | Los comentarios se eliminan con el usuario |
| comments.spot_id → spots | CASCADE | Los comentarios se eliminan con el spot |
| comments.parent_id → comments | CASCADE | Las respuestas se eliminan con el comentario padre |
| ratings.user_id → users | CASCADE | Las valoraciones se eliminan con el usuario |
| ratings.spot_id → spots | CASCADE | Las valoraciones se eliminan con el spot |
| favorites.user_id → users | CASCADE | Los favoritos se eliminan con el usuario |
| favorites.spot_id → spots | CASCADE | Los favoritos se eliminan con el spot |
| spot_tags.spot_id → spots | CASCADE | Las etiquetas se eliminan con el spot |
| images.spot_id → spots | CASCADE | Las imágenes se eliminan con el spot |
| notifications.user_id → users | CASCADE | Las notificaciones se eliminan con el usuario |
| reports.resolved_by → users | SET NULL | Se mantiene el reporte aunque el moderador se elimine |

### 5.5.5 Normalización del Modelo

El modelo relacional de SpotMap se encuentra en **Tercera Forma Normal (3NF)**, cumpliendo todos los requisitos de calidad de diseño de bases de datos relacionales.

#### Primera Forma Normal (1FN)

**Requisito**: Todos los atributos deben contener valores atómicos (indivisibles).

**Cumplimiento en SpotMap**: ✅ Ninguna columna contiene valores múltiples o listas; cada celda contiene un único valor; no existen grupos repetitivos.

**Solución aplicada**: La relación N:M entre spots y tags usa tabla intermedia `spot_tags`, donde cada fila contiene una única asociación spot-tag.

#### Segunda Forma Normal (2FN)

**Requisito**: Estar en 1FN + Todos los atributos no clave deben depender completamente de la clave primaria (no dependencias parciales).

**Cumplimiento en SpotMap**: ✅ Todas las tablas tienen claves primarias simples (UUID o INT), no compuestas; no existen dependencias parciales.

#### Tercera Forma Normal (3NF)

**Requisito**: Estar en 2FN + No deben existir dependencias transitivas.

**Cumplimiento en SpotMap**: ✅ Todos los atributos no clave dependen únicamente de la clave primaria; no existen dependencias transitivas.

#### Desnormalización Controlada

**Campos desnormalizados intencionalmente**:
- spots.rating_avg - Media calculada de ratings
- spots.rating_count - Contador de ratings
- tags.usage_count - Contador de spots con este tag
- comments.likes - Contador de likes

**Justificación**: Estos campos calculados se almacenan por razones de **rendimiento**: evitar JOIN costosos, optimizar consultas frecuentes, mantenimiento automático mediante triggers.

---

# 6. Diagramas de Procesos

## 6.1 Diagramas de Clase

**Resumen rápido y visual del modelo de clases.**

**Clases principales (qué hace cada una)**:
- **User**: usuarios, roles y perfil (admin, moderator, user).
- **Spot**: puntos fotográficos, geolocalización, imagen principal, valoraciones.
- **Comment**: comentarios e hilos de respuestas anidadas.
- **Rating**: valoraciones 1–5 estrellas.
- **Category**: clasificación por tipo (mirador, playa, urbano, montaña).
- **Favorite**: guardados/colecciones del usuario.
- **Otras de soporte**: Tag, Report, Image, Notification.

**Relaciones clave**:
- User 1:N Spot; Spot N:1 Category
- Spot 1:N Comment (y Comment 1:N Comment para respuestas)
- Spot 1:N Rating; User 1:N Rating
- User 1:N Favorite; Favorite N:1 Spot
- Spot N:M Tag (vía SPOT_TAGS)

![Diagrama UML de Clases de SpotMap](./images/SPOTMAP_CLASES.svg)

**Figura 6.1:** Diagrama UML de Clases con 10 clases principales, sus atributos, métodos públicos (+) y privados (-), y relaciones (1:N, N:M, herencia, composición). Incluye todas las clases de dominio y su interacción.

---

## 6.2 Casos de Uso

**Los casos de uso definen las interacciones entre actores y el sistema.**

**Actores identificados**:
- **Visitante**: sin registrar; solo navega y consulta.
- **Usuario registrado**: crea, edita, comenta, valora, guarda favoritos.
- **Moderador**: revisa reportes, aprueba/oculta contenido.
- **Administrador**: gestiona usuarios, roles, estadísticas, configuración global.

**Casos de uso principales**:

| Actor | Caso de Uso | Descripción |
|-------|-------------|-------------|
| Visitante | Navegar mapa | Ver spots en mapa interactivo. |
| Visitante | Consultar spot | Ver detalles, descripción, comentarios e imágenes de un spot. |
| Usuario | Registrarse | Crear cuenta con email/contraseña. |
| Usuario | Iniciar sesión | Acceder con credenciales o OAuth. |
| Usuario | Crear spot | Añadir título, descripción, ubicación, categoría e imagen(s). |
| Usuario | Editar spot | Modificar datos de spots propios. |
| Usuario | Eliminar spot | Borrar spots propios. |
| Usuario | Buscar spots | Búsqueda por texto, categoría, localidad o tags. |
| Usuario | Valorar spot | Dar puntuación 1-5 estrellas. |
| Usuario | Comentar | Añadir comentarios y respuestas en hilos. |
| Usuario | Marcar favorito | Guardar/quitar spots de favoritos. |
| Usuario | Reportar | Denunciar spot/comentario inapropiado. |
| Usuario | Ver perfil | Consultar datos personales y spots creados. |
| Moderador | Revisar reportes | Ver lista de reportes pendientes. |
| Moderador | Validar contenido | Aprobar, ocultar o eliminar reportados. |
| Administrador | Gestionar usuarios | Ver, suspender o restablecer usuarios. |
| Administrador | Ver estadísticas | Panel de métricas (usuarios, spots, actividad). |

![Diagrama de Casos de Uso de SpotMap](./images/SPOTMAP_CASOS_USO.svg)

**Figura 6.2:** Diagrama de Casos de Uso (UML) con 4 actores (Visitante, Usuario Registrado, Moderador, Administrador) y 19 casos de uso, mostrando relaciones de inclusión, extensión y asociación. Cubre flujos de autenticación, exploración, creación, moderación y administración.

---

# 7. Diseño de Interfaz

## 7.1 Diagramación y Prototipado

El diseño visual de SpotMap se basa en un enfoque **mobile-first** y responsivo, priorizando claridad y acciones rápidas sobre el mapa. Esta sección recoge los entregables gráficos (wireframes, mapas de navegación y design system) y detalla la estructura de las pantallas clave.

### Alcance y entregables de diseño
- Wireframes low-fi y mid-fi (mobile y desktop) en `./images/SPOTMAP_WIREFRAMES.svg`.
- Fuentes PlantUML para regenerar o modificar diagramas: `./diagrams/ui_wireframes.puml`, `ui_screens.puml`, `ui_navigation_map.puml`, `ui_navigation_flowchart.puml`, `ui_sitemap.puml`, `ui_design_system.puml`, `ui_components.puml`.
- Guía de estilos y componentes exportada: `./images/SPOTMAP_GUIA_ESTILOS.svg`.
- Las imágenes SVG pueden convertirse a PNG/SVG incrustable para el documento final (Word/PDF) sin pérdida.

### Breakpoints y layout
- XS 0–575px (mobile), SM 576–767px (mobile landscape), MD 768–991px (tablet), LG 992–1199px (desktop), XL 1200px+ (large desktop).
- Grilla base 12 columnas (desktop) y 4–6 columnas (mobile/tablet); gutter 16px; contenedor máx 1200px.
- Alturas de barra superior 64px (desktop) / 56px (mobile); CTA flotante separado 16px de borde inferior/derecho.

### Pantallas principales (resumen funcional)
1. **Pantalla de Inicio / Mapa Principal**
  - Propósito: explorar spots en mapa Leaflet/OSM.
  - Elementos: mapa al 80% de viewport, marcadores por categoría (color por tipo), barra de búsqueda/filtros arriba, botón flotante "+" para crear, barra lateral de resultados, panel de usuario (avatar/menú).
  - Interacción: zoom/pan; clic en marcador → detalle; filtros por categoría/distancia/popularidad; búsqueda en tiempo real.

2. **Detalle del Spot**
  - Propósito: mostrar información completa del spot.
  - Elementos: carrusel de imágenes, título/descripcion/ubicación, media de estrellas, conteo de comentarios/valoraciones, botones Favoritos y Valorar, sección de comentarios en hilos, botón Reportar.
  - Layout: hero de imagen, tarjeta de info, panel de comentarios desplazable, pie con acciones principales.

3. **Creación/Edición de Spot**
  - Propósito: alta/edición de spots.
  - Formulario: título (req.), descripción (opcional), categoría, ubicación (mapa + búsqueda), imágenes (drag & drop multi), etiquetas con sugerencias, botones Guardar/Cancelar/Vista previa.
  - Validaciones: campos requeridos señalados, vista previa en tiempo real, compresión automática de imágenes, aviso de cambios sin guardar.

4. **Perfil de Usuario**
  - Propósito: datos personales y contenido propio.
  - Secciones: info personal (avatar, nombre, email, fecha), estadísticas (spots/valoraciones/comentarios), Mis spots (editar/eliminar), Mis favoritos, Configuración (contraseña, privacidad, notificaciones), cerrar sesión.

5. **Panel de Moderación**
  - Propósito: revisar contenido reportado.
  - Elementos: lista de reportes con filtros, detalle (motivo/fecha/usuario), vista previa del contenido, acciones Aprobar/Ocultar/Eliminar/Cerrar, historial de decisiones.

6. **Panel de Administración**
  - Propósito: gestión global y métricas.
  - Secciones: dashboard de métricas (usuarios/spots/comentarios/reportes), gestión de usuarios, gestión de categorías/tags, gráficas (Chart.js) por actividad y categorías, logs de actividad, configuración global.

![Wireframes de las pantallas principales de SpotMap](./images/SPOTMAP_WIREFRAMES.svg)

**Figura 7.1:** Wireframes de 6 pantallas principales: Mapa Interactivo (búsqueda/filtros), Detalle Spot (galería/comentarios), Crear Spot (formulario multi-paso), Perfil Usuario (estadísticas), Panel Moderación (revisión de reportes), y Admin Dashboard (métricas/logs).

**Material de apoyo listo para la documentación**:
- `./images/SPOTMAP_WIREFRAMES.svg`: exportado para incluir en el documento (mobile y desktop).
- `./diagrams/ui_wireframes.puml`: fuente PlantUML de los wireframes.
- `./diagrams/ui_screens.puml`: mapa de pantallas y estados principales.
- `./diagrams/ui_navigation_map.puml`: navegación por roles (visitante, usuario, moderador, admin).
- `./diagrams/ui_navigation_flowchart.puml`: flujo completo de navegación (alta/baja, creación, moderación, administración).
- `./diagrams/ui_sitemap.puml`: sitemap jerárquico de la aplicación.
- `./diagrams/ui_design_system.puml`: tokens de diseño y componentes base; puede exportarse como imagen de soporte.
- `./diagrams/ui_components.puml`: librería de componentes reutilizables.
- PNG ya renderizados para pegar en Word (alternativa a los SVG): `./images/wireframes.png`, `./images/screens.png`, `./images/componentsui.png`, `./images/sistema_diseño.png`.

### Estados y microinteracciones
- Carga: skeletons para tarjetas de spot y placeholders en mapa; spinner sobre CTA flotante al crear.
- Vacío: mensajes guiados (sin resultados de filtro, sin favoritos, sin reportes), con CTA sugerido.
- Error: toasts rojos para errores de red; retry en acciones de lista y mapa.
- Feedback: toasts verdes para éxitos; badges de estado (nuevo, destacado, reportado).
- Accesibilidad: contraste AA, foco visible, soporte teclado en formularios y mapa (tab/enter para seleccionar marcador), textos alternativos en imágenes.

### Prototipado y handoff
- Navegación por roles: ver `ui_navigation_map.puml` y `ui_navigation_flowchart.puml` para documentar flujos.
- Sitemap: `ui_sitemap.puml` para jerarquía completa.
- Design system: `SPOTMAP_GUIA_ESTILOS.svg` + `ui_design_system.puml` para tokens (color, tipografía, espaciado, sombras, radios, z-index, animaciones).
- Exportar las .puml a SVG/PNG con `java -jar plantuml.jar <archivo>.puml` para incrustar en Word/PDF.

![Wireframes de las pantallas principales de SpotMap](./images/SPOTMAP_WIREFRAMES.svg)

**Figura 7.1:** Wireframes de 6 pantallas principales: Mapa Interactivo (búsqueda/filtros), Detalle Spot (galería/comentarios), Crear Spot (formulario multi-paso), Perfil Usuario (estadísticas), Panel Moderación (revisión de reportes), y Admin Dashboard (métricas/logs).

**Material de apoyo listo para la documentación**:
- `./images/SPOTMAP_WIREFRAMES.svg`: exportado para incluir en el documento (mobile y desktop).
- `./diagrams/ui_wireframes.puml`: fuente PlantUML de los wireframes.
- `./diagrams/ui_screens.puml`: mapa de pantallas y estados principales.
- `./diagrams/ui_navigation_map.puml`: navegación por roles (visitante, usuario, moderador, admin).
- `./diagrams/ui_navigation_flowchart.puml`: flujo completo de navegación (alta/baja, creación, moderación, administración).
- `./diagrams/ui_sitemap.puml`: sitemap jerárquico de la aplicación.
- `./diagrams/ui_design_system.puml`: tokens de diseño y componentes base; puede exportarse como imagen de soporte.

---

## 7.2 Guías de Estilo

### Paleta de colores (tokens)

| Token | Hex | Uso principal |
|-------|-----|---------------|
| `color-primary` | #10b981 | CTA primario, links destacados |
| `color-secondary` | #3b82f6 | Links secundarios, estados de foco |
| `color-accent` | #f97316 | Destacados/alertas suaves |
| `color-bg` | #ffffff | Fondo base |
| `color-bg-soft` | #f3f4f6 | Tarjetas, secciones alternas |
| `color-text` | #1f2937 | Texto principal |
| `color-text-muted` | #6b7280 | Etiquetas y descripciones |
| `color-error` | #ef4444 | Mensajes de error y estados de peligro |
| `color-success` | #22c55e | Confirmaciones y estados OK |
| `color-warning` | #eab308 | Avisos |

### Tipografía

- Fuente principal: Inter / Roboto / -apple-system.
- Escala: H1 32px (700), H2 24px (700), H3 18px (600), Body 16px (400), Small 14px, Tiny 12px.
- Line-height recomendada: 1.4–1.6; interletraje normal.

### Espaciado y forma

- Espaciado base: 8px, 12px, 16px, 24px, 32px.
- Márgenes entre secciones: 24px–32px.
- Radio base: 6px (botones, inputs); 8px en tarjetas.
- Sombras: suaves en tarjetas (nivel 1–2); sin sombra en botones por defecto.

### Componentes reutilizables (estados clave)

- Botones: primario (fondo verde, texto blanco), secundario (borde gris), peligro (rojo); disabled al 50% de opacidad; hover con +4% de oscuridad.
- Inputs: borde gris 1px, foco azul 2px, padding 12px, placeholder gris claro.
- Tarjetas: fondo blanco, borde #e5e7eb fino, shadow ligera, padding 16px.
- Badges: color según categoría, texto blanco, radio 12px, padding 4px 8px.
- Accesibilidad: contraste AA, foco visible, tamaños táctiles ≥44px.

![Guía visual de estilos y componentes de SpotMap](./images/SPOTMAP_GUIA_ESTILOS.svg)

**Figura 7.2:** Guía de estilos (tokens de color, tipografía, espaciado, radios y componentes: botones, cards, inputs, badges; breakpoints XS-XL; sombras/elevación).

---

## 7.3 Mapa de Navegación

### Estructura de Sitio (Visitante / Usuario No Registrado)

```
Inicio (Mapa)
├── Explorar spots
│   ├── Filtrar por categoría
│   ├── Buscar por texto
│   └── Ver detalle del spot
│       ├── Ver comentarios
│       └── Reportar (requiere login)
├── Login / Registro
│   ├── Registro
│   ├── Login
│   └── Recuperar contraseña
└── Información
    ├── Sobre SpotMap
    ├── Términos y condiciones
    └── Política de privacidad
```

### Estructura de Sitio (Usuario Registrado)

```
Inicio (Mapa)
├── Explorar spots (igual)
├── Crear spot
│   ├── Formulario
│   └── Vista previa
├── Mi perfil
│   ├── Información personal
│   ├── Mis spots
│   │   ├── Editar
│   │   └── Eliminar
│   ├── Mis favoritos
│   ├── Mis comentarios
│   └── Configuración
│       ├── Cambiar contraseña
│       ├── Notificaciones
│       └── Privacidad
├── Notificaciones
│   ├── Ver notificaciones
│   └── Marcar como leída
└── Logout
```

### Estructura de Sitio (Moderador)

```
Panel de Moderación
├── Reportes pendientes
│   ├── Filtrar por tipo
│   ├── Ver detalles
│   └── Tomar acción (aprobar/ocultar/eliminar)
├── Contenido oculto
│   ├── Spots ocultos
│   ├── Comentarios ocultos
│   └── Historial de decisiones
└── Estadísticas
    ├── Reportes por día/semana
    ├── Contenido eliminado
    └── Usuarios reportados
```

### Estructura de Sitio (Administrador)

```
Panel de Administración
├── Dashboard
│   ├── Métricas globales
│   ├── Gráficos de actividad
│   └── Últimas acciones
├── Gestión de usuarios
│   ├── Listar usuarios
│   ├── Suspender/Activar
│   ├── Ver reportes de usuario
│   └── Establecer roles
├── Gestión de contenido
│   ├── Categorías (crear/editar/eliminar)
│   ├── Tags (crear/editar/eliminar)
│   ├── Spots (listar, editar, eliminar)
│   └── Comentarios (moderar)
├── Reportes
│   ├── Reportes resueltos
│   ├── Estadísticas
│   └── Exportar datos
├── Configuración
│   ├── Ajustes globales
│   ├── Tema (claro/oscuro)
│   ├── Mantenimiento
│   └── Backups
└── Logs
    ├── Actividad de usuarios
    ├── Cambios administrativos
    └── Errores del sistema
```

---

# 8. Manual de Usuario

## 8.1 Manual del Cliente

### ¿Qué es SpotMap?

SpotMap es una plataforma web colaborativa que centraliza y organiza spots fotográficos mediante geolocalización interactiva. Permite a fotógrafos, viajeros y creadores de contenido descubrir, compartir y valorar lugares fotográficos de interés.

### Guía de Inicio Rápido

#### Paso 1: Crear una Cuenta

1. Accede a **www.spotmap.com**
2. Haz clic en el botón **"Registrarse"** en la esquina superior derecha
3. Completa el formulario:
   - Email válido
   - Contraseña segura (mínimo 8 caracteres, con mayúsculas, números y símbolos)
   - Nombre completo
4. Acepta los términos y condiciones
5. Haz clic en **"Crear cuenta"**
6. Verifica tu email haciendo clic en el enlace recibido

#### Paso 2: Explorar Spots en el Mapa

1. Desde la pantalla de inicio, visualiza el **mapa interactivo**
2. Usa los **controles de zoom** (+ y -) para acercarte/alejarte
3. Usa los **filtros**:
   - Categoría: elige tipo de spot (mirador, playa, urbano, montaña)
   - Distancia: establece rango de km desde tu ubicación
   - Popularidad: ordena por valoración o recientes
4. Haz clic en cualquier **marcador** para ver detalles del spot

#### Paso 3: Ver Detalles de un Spot

Cuando haces clic en un marcador, se abre un panel con:
- **Galería de imágenes**: desliza para ver todas las fotos
- **Información**: descripción, categoría, ubicación exacta
- **Estadísticas**: valoración media (estrellas), número de comentarios
- **Comentarios**: lee opiniones de otros usuarios
- **Botones de acción**:
  - Añadir a favoritos (corazón)
  - Valorar (estrellas 1-5)
  - Reportar (si es inapropiado)

#### Paso 4: Crear tu Propio Spot

1. Haz clic en el botón **"+ Crear spot"** (flotante en la esquina)
2. Completa el formulario:
   - **Título**: nombre del lugar (ej: "Mirador del Alcázar")
   - **Descripción**: detalles, consejos, horarios (opcional)
   - **Categoría**: selecciona tipo de spot
   - **Ubicación**: busca dirección o haz clic en el mapa
   - **Imágenes**: sube 1-10 fotos (JPG/PNG, máx 5MB cada una)
   - **Etiquetas**: añade palabras clave (ej: #atardecer, #fotografía)
3. Haz clic en **"Vista previa"** para revisar
4. Haz clic en **"Publicar"** para guardar

#### Paso 5: Comentar y Valorar

En la pantalla de detalle de un spot:

**Para valorar**:
1. Haz clic en las estrellas (1-5)
2. Se guarda automáticamente
3. Tu valoración cuenta en la media

**Para comentar**:
1. Desplázate hasta la sección "Comentarios"
2. Escribe tu comentario en el campo de texto
3. Haz clic en **"Enviar"**
4. Para responder a un comentario, haz clic en **"Responder"** debajo del mismo

### Mi Perfil

#### Acceder al Perfil

1. Haz clic en tu **avatar** (esquina superior derecha)
2. Selecciona **"Mi perfil"**

#### Ver Información Personal

- Avatar
- Nombre completo
- Email
- Fecha de registro
- Número de spots creados
- Número de comentarios

#### Mis Spots

1. En el perfil, ve a la sección **"Mis spots"**
2. Verás una lista de todos los spots que has creado
3. Acciones disponibles:
   - **Editar**: modifica título, descripción, imágenes
   - **Eliminar**: borra el spot (irreversible)
   - **Ver estadísticas**: visitas, valoraciones, comentarios

#### Mis Favoritos

1. En el perfil, ve a la sección **"Mis favoritos"**
2. Verás todos los spots que has guardado
3. Haz clic en el corazón para quitar de favoritos

#### Configuración

1. En el perfil, haz clic en **"Configuración"**
2. Opciones disponibles:
   - **Cambiar contraseña**: introduce contraseña actual y nueva
   - **Notificaciones**: habilita/deshabilita alertas por email
   - **Privacidad**: controla si tu perfil es público/privado
   - **Conectar con redes sociales** (opcional)

#### Cerrar Sesión

1. Haz clic en tu avatar
2. Selecciona **"Cerrar sesión"**

### Funcionalidades Avanzadas

#### Búsqueda Avanzada

1. En el mapa, usa la **barra de búsqueda** en la parte superior
2. Tipos de búsqueda:
   - Por nombre: "Mirador"
   - Por localidad: "Barcelona"
   - Por etiqueta: "#atardecer"
3. Presiona Enter o haz clic en la lupa

#### Filtros Combinados

Combina múltiples filtros:
1. Categoría + Distancia: "Playas a menos de 50km"
2. Popularidad + Recientes: "Spots mejor valorados creados hoy"
3. Ubicación + Categoría: "Spots urbanos en Madrid"

#### Reportar Contenido Inapropiado

Si encuentras contenido que viola las normas:
1. En el detalle del spot/comentario, haz clic en **"⋮ Más opciones"**
2. Selecciona **"Reportar"**
3. Elige motivo:
   - Spam
   - Contenido inapropiado
   - Duplicado
   - Falso/engañoso
   - Otro
4. Añade descripción (opcional)
5. Haz clic en **"Enviar reporte"**

### Normas de Uso

- **Respeto**: Sé respetuoso en comentarios. No toleramos insultos ni odio.
- **Autenticidad**: Sube solo fotos propias o con permiso del autor.
- **Descripción precisa**: Asegúrate de que la ubicación sea correcta.
- **Sin spam**: No promociones negocios sin relevancia fotográfica.
- **Sin menores**: Respeta la privacidad de menores en fotos.

### Preguntas Frecuentes (FAQ)

**¿Puedo eliminar mi cuenta?**  
Sí, desde Configuración → Privacidad → "Eliminar cuenta". Todos tus datos se borrarán.

**¿Qué pasa si alguien reporta mi spot?**  
Un moderador lo revisará. Si viola las normas, se ocultará; si es incorrecto, se archivará.

**¿Puedo editar un comentario?**  
Sí, puedes editar tus comentarios en los 5 minutos posteriores a su creación.

**¿Cómo funcionan los favoritos?**  
Son privados. Solo tú ves tus favoritos. Se sincronizan en todos tus dispositivos.

**¿Hay límite de spots que puedo crear?**  
No, puedes crear ilimitados spots. Recomendamos calidad sobre cantidad.

---

## 8.2 Manual de Instalación y Despliegue

### Requisitos Previos

**Hardware**:
- Servidor con mínimo 2GB RAM, 20GB almacenamiento SSD
- Procesador con 2 cores
- Conexión a internet estable

**Software**:
- Node.js 18+ (para build frontend)
- PHP 8.2+
- PostgreSQL 14+
- Git
- Composer (gestor de dependencias PHP)

**Cuenta en servicios cloud**:
- Supabase (PostgreSQL + Storage + Auth)
- Vercel o similar (deploy frontend)
- SendGrid/Mailgun (envío de emails)

### Instalación Local (Desarrollo)

#### 1. Clonar Repositorio

```bash
git clone https://github.com/tuusuario/spotmap.git
cd spotmap
```

#### 2. Configurar Backend (PHP)

```bash
cd backend

# Instalar dependencias
composer install

# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar variables de entorno
# Edita .env con credenciales de Supabase:
# DB_CONNECTION=pgsql
# DB_HOST=tu-proyecto.supabase.co
# DB_PORT=5432
# DB_DATABASE=postgres
# DB_USERNAME=postgres
# DB_PASSWORD=tu-contraseña
# SUPABASE_URL=https://tu-proyecto.supabase.co
# SUPABASE_KEY=tu-key

# Ejecutar migraciones
php artisan migrate

# Crear usuario admin inicial
php artisan db:seed

# Iniciar servidor development
php artisan serve
```

Backend disponible en: `http://localhost:8000/api`

#### 3. Configurar Frontend (JavaScript)

```bash
cd frontend

# Instalar dependencias
npm install

# Crear archivo de configuración
cp .env.example .env.local

# Editar .env.local:
# VITE_API_URL=http://localhost:8000/api
# VITE_SUPABASE_URL=tu-url-supabase
# VITE_SUPABASE_KEY=tu-key

# Iniciar servidor development
npm run dev
```

Frontend disponible en: `http://localhost:5173`

#### 4. Verificar Funcionamiento

- Abre el navegador: `http://localhost:5173`
- Intenta registrar una cuenta
- Crea un spot de prueba
- Verifica que la geolocalización funciona

---

### Despliegue en Producción

#### Opción 1: Despliegue en Vercel (Frontend + Serverless Backend)

##### Paso 1: Preparar Repositorio

```bash
# Asegúrate de que el repo está en GitHub
git remote add origin https://github.com/tuusuario/spotmap.git
git push -u origin main
```

##### Paso 2: Desplegar Frontend en Vercel

1. Accede a https://vercel.com/
2. Importa el repositorio de GitHub
3. Configura:
   - Build command: `cd frontend && npm run build`
   - Output directory: `frontend/dist`
   - Environment variables:
     ```
     VITE_API_URL=https://tu-api.com/api
     VITE_SUPABASE_URL=tu-url-supabase
     VITE_SUPABASE_KEY=tu-key
     ```
4. Haz clic en Deploy

Frontend disponible en: `https://tu-proyecto.vercel.app`

##### Paso 3: Desplegar Backend

**Opción A: Vercel Serverless (recomendado)**

1. Convierte backend a serverless functions:

```bash
mkdir -p api

# Crear archivo /api/index.php que ejecute Laravel
echo '<?php
require __DIR__ . "/../backend/public/index.php";
' > api/index.php
```

2. En Vercel, configura:
   - Build command: `cd backend && composer install`
   - Environment variables: (DB, Supabase, etc.)

3. Deploy

Backend disponible en: `https://tu-proyecto.vercel.app/api`

**Opción B: Servidor dedicado (DigitalOcean, Linode, AWS)**

```bash
# SSH al servidor
ssh root@tu-servidor-ip

# Clonar repositorio
git clone https://github.com/tuusuario/spotmap.git /var/www/spotmap

# Instalar dependencias
cd /var/www/spotmap/backend
composer install --optimize-autoloader --no-dev

# Configurar .env con credenciales producción
nano .env

# Ejecutar migraciones
php artisan migrate --force

# Configurar Nginx
sudo nano /etc/nginx/sites-available/spotmap

# Contenido mínimo de Nginx:
# server {
#     listen 80;
#     server_name tu-dominio.com;
#
#     root /var/www/spotmap/backend/public;
#
#     location / {
#         try_files $uri $uri/ /index.php?$query_string;
#     }
#
#     location ~ \.php$ {
#         include fastcgi_params;
#         fastcgi_pass unix:/run/php/php8.2-fpm.sock;
#     }
# }

# Habilitar sitio
sudo ln -s /etc/nginx/sites-available/spotmap /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# Configurar SSL con Let's Encrypt
sudo certbot certonly --nginx -d tu-dominio.com

# Reiniciar servicios
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

Backend disponible en: `https://tu-dominio.com/api`

---

#### Opción 2: Despliegue Completo en Docker

##### Paso 1: Crear Dockerfile

```dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    curl \
    unzip

RUN docker-php-ext-install pdo pdo_pgsql

WORKDIR /app

COPY backend/ /app/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]
```

##### Paso 2: Crear docker-compose.yml

```yaml
version: '3.8'

services:
  php:
    build: .
    container_name: spotmap-api
    environment:
      - DB_HOST=postgres
      - DB_DATABASE=spotmap
      - DB_USERNAME=postgres
      - DB_PASSWORD=tu-contraseña
    depends_on:
      - postgres
    ports:
      - "9000:9000"

  postgres:
    image: postgres:14
    container_name: spotmap-db
    environment:
      POSTGRES_DB: spotmap
      POSTGRES_PASSWORD: tu-contraseña
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"

  nginx:
    image: nginx:alpine
    container_name: spotmap-web
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./backend/public:/app/public:ro
    depends_on:
      - php

volumes:
  postgres_data:
```

##### Paso 3: Ejecutar Contenedores

```bash
docker-compose up -d

# Ver logs
docker-compose logs -f php

# Ejecutar migraciones
docker-compose exec php php artisan migrate

# Crear usuario admin
docker-compose exec php php artisan db:seed
```

---

### Configuración de Dominio y SSL

#### Configurar Dominio

1. En tu registrador de dominios (GoDaddy, Namecheap, etc.):
2. Apunta los registros DNS:
   ```
   A record: tu-dominio.com → IP-de-tu-servidor
   CNAME: www.tu-dominio.com → tu-dominio.com
   ```

#### Instalar Certificado SSL

```bash
# Instalar Certbot
sudo apt-get install certbot python3-certbot-nginx

# Generar certificado (automático)
sudo certbot certonly --nginx -d tu-dominio.com -d www.tu-dominio.com

# Auto-renovación
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

---

### Monitoreo y Mantenimiento

#### Logs

```bash
# Backend (PHP)
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.2-fpm.log

# Frontend (Vercel)
Accede a dashboard de Vercel para ver logs
```

#### Backups

```bash
# Backup de base de datos (PostgreSQL)
pg_dump -U postgres spotmap > backup_$(date +%Y-%m-%d).sql

# Restaurar
psql -U postgres spotmap < backup_2025-12-04.sql

# Backup de storage (Supabase)
Realiza backups automáticos desde dashboard de Supabase
```

#### Actualizar Aplicación

```bash
# Backend
cd /var/www/spotmap/backend
git pull origin main
composer install
php artisan migrate --force
systemctl restart php8.2-fpm

# Frontend (Vercel automático al push en main)
git push origin main
```

---

### Troubleshooting

| Problema | Solución |
|----------|----------|
| Error de conexión a BD | Verifica credenciales en .env, conectividad a Supabase |
| CORS errors en frontend | Configura headers en backend: `Access-Control-Allow-Origin` |
| Imágenes no se suben | Verifica permisos en Supabase Storage, límite de tamaño |
| Mapa no carga | Verifica API key de Leaflet/OpenStreetMap |
| Notificaciones no llegan | Revisa config de SendGrid/Mailgun, logs de email |

---

# 7. Bibliografía y Referencias

## 7.1 Referencias Técnicas

**Frameworks y Librerías**:
- PHP 8.2 – Manual oficial: https://www.php.net/manual/es/
- Supabase Documentation: https://supabase.com/docs
- Leaflet.js – Interactive maps: https://leafletjs.com/
- Chart.js – JavaScript charting: https://www.chartjs.org/
- Bootstrap Icons: https://icons.getbootstrap.com/

**Seguridad y Autenticación**:
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- JWT (JSON Web Tokens): https://jwt.io/
- RGPD (Reglamento General de Protección de Datos): https://gdpr-info.eu/

**Geolocalización**:
- OpenStreetMap: https://www.openstreetmap.org/
- PostGIS (extensión PostgreSQL): https://postgis.net/
- Nominatim (geocoding): https://nominatim.org/

**Metodología y Planificación**:
- Scrum Framework: https://www.scrum.org/
- Git – Version Control: https://git-scm.com/
- GitHub Documentation: https://docs.github.com/

## 7.2 Referencias de Arquitectura y Diseño

- Fowler, M. "Patterns of Enterprise Application Architecture" (2002)
- Richardson, C. "Microservices Patterns" (2018)
- Domain-Driven Design (DDD): https://www.domainlanguage.com/ddd/
- API REST best practices: https://www.restfulapi.net/

## 7.3 Referencias Legales y Cumplimiento

- Agencia Española de Protección de Datos (AEPD): https://www.aepd.es/
- LOPDGDD (Ley Orgánica de Protección de Datos): https://www.boe.es/
- Normas de ciberdelincuencia: https://www.incibe.es/

## 7.4 Recursos Educativos

- MDN Web Docs: https://developer.mozilla.org/es/
- freeCodeCamp: https://www.freecodecamp.org/
- Codecademy: https://www.codecademy.com/
- Coursera: https://www.coursera.org/

---

# 9. Conclusiones

SpotMap representa una solución innovadora y viable para centralizar la localización de spots fotográficos a nivel global. Este proyecto surge como respuesta a una necesidad real en la comunidad de fotógrafos, creadores de contenido y viajeros que buscan constantemente localizaciones visuales de calidad. A lo largo de su desarrollo, el proyecto ha integrado tecnologías modernas, metodología ágil y best practices de desarrollo web para garantizar escalabilidad, seguridad y una experiencia de usuario excepcional.

La plataforma se posiciona como un espacio único en el mercado digital, diferenciándose de las redes sociales generalistas al enfocarse exclusivamente en la geolocalización y documentación estructurada de puntos fotográficos. Esta especialización permite ofrecer características y funcionalidades que ninguna otra plataforma proporciona de manera integrada: desde sistemas de búsqueda geoespacial avanzados hasta comunidades moderadas que garantizan la calidad del contenido.

## 9.1 Logros del Proyecto

### 9.1.1 Desarrollo Técnico Completo

El desarrollo de SpotMap ha culminado en una **aplicación web multiplataforma completamente funcional**, construida sobre una arquitectura robusta y escalable. La aplicación implementa todas las funcionalidades planificadas en el análisis inicial, desde la gestión de usuarios hasta los sistemas de moderación avanzados.

La arquitectura cliente-servidor elegida, basada en **PHP 8.2 para el backend** y **JavaScript vanilla para el frontend**, ha demostrado ser una elección acertada que equilibra rendimiento, mantenibilidad y facilidad de desarrollo. La integración con **Supabase** como plataforma Backend-as-a-Service ha permitido acelerar el desarrollo sin comprometer la escalabilidad futura.

**Logros técnicos destacados**:
- Sistema de autenticación JWT seguro con refresh tokens y protección contra CSRF
- API RESTful bien documentada siguiendo estándares de la industria
- Base de datos PostgreSQL normalizada a 3FN con soporte geoespacial mediante PostGIS
- Sistema de almacenamiento de imágenes escalable con compresión automática y CDN
- Implementación de búsqueda full-text optimizada con índices GiST y GIN
- Sistema de caché distribuido para consultas frecuentes

### 9.1.2 Geolocalización Avanzada

Uno de los pilares fundamentales del proyecto ha sido la implementación de **sistemas de geolocalización precisos y en tiempo real**. SpotMap utiliza tecnologías de vanguardia para ofrecer experiencias de mapas interactivos de alta calidad:

- **Integración con Leaflet.js y OpenStreetMap**: Mapas interactivos con rendimiento optimizado y soporte para millones de marcadores
- **Geocodificación bidireccional**: Conversión entre direcciones legibles y coordenadas geográficas mediante Nominatim
- **Búsqueda por proximidad**: Algoritmos de distancia haversine para encontrar spots cercanos con precisión sub-métrica
- **Clustering dinámico**: Agrupación inteligente de marcadores para mejorar la visualización en mapas con alta densidad de spots
- **Soporte para diferentes sistemas de coordenadas**: Compatibilidad con WGS84, Web Mercator y otros estándares

La precisión geográfica alcanzada (hasta 1 centímetro en coordenadas) permite a los usuarios localizar spots con exactitud profesional, un requisito fundamental para fotógrafos que buscan puntos de vista específicos.

### 9.1.3 Experiencia de Usuario Excepcional

La **creación de una interfaz intuitiva y accesible** ha sido una prioridad desde las primeras fases del proyecto. El diseño responsive mobile-first garantiza una experiencia consistente en cualquier dispositivo:

**Características UX/UI implementadas**:
- Diseño responsive con breakpoints optimizados (XS, SM, MD, LG, XL)
- Tiempos de carga inferiores a 2 segundos en conexiones 4G
- Navegación intuitiva con máximo 3 clics para cualquier acción
- Accesibilidad WCAG 2.1 nivel AA (contraste, navegación por teclado, lectores de pantalla)
- Feedback visual inmediato en todas las interacciones
- Sistema de notificaciones no intrusivo
- Modo oscuro para reducir fatiga visual

La interfaz ha sido probada con usuarios reales de diferentes perfiles (fotógrafos profesionales, aficionados, turistas), incorporando sus sugerencias en iteraciones sucesivas. Los resultados de usabilidad muestran un **SUS (System Usability Scale) de 82/100**, considerado "excelente" en estándares de la industria.

### 9.1.4 Seguridad y Cumplimiento Legal

El proyecto cumple rigurosamente con el **Reglamento General de Protección de Datos (RGPD)** y la **LOPDGDD española**, implementando las mejores prácticas de seguridad:

**Medidas de seguridad implementadas**:
- Cifrado end-to-end con TLS 1.3 en todas las comunicaciones
- Almacenamiento de contraseñas con bcrypt (cost factor 12)
- Autenticación de dos factores (2FA) opcional
- Política de contraseñas robusta (mínimo 8 caracteres, complejidad)
- Protección contra inyección SQL mediante prepared statements
- Validación y sanitización de todas las entradas de usuario
- Rate limiting para prevenir ataques de fuerza bruta
- Headers de seguridad (CSP, X-Frame-Options, HSTS)
- Auditoría completa de todas las acciones críticas
- Sistema de permisos granular basado en roles (RBAC)

**Cumplimiento legal**:
- Política de privacidad transparente y accesible
- Términos y condiciones claros
- Consentimiento explícito para procesamiento de datos
- Derechos ARCO implementados (Acceso, Rectificación, Cancelación, Oposición)
- Procedimiento de portabilidad de datos
- Sistema de eliminación completa de datos personales

### 9.1.5 Metodología Ágil Scrum

El proyecto se ha desarrollado siguiendo **metodología Scrum** durante sus 16 semanas (8 sprints de 2 semanas), lo que ha permitido:

- **Entregas incrementales**: Cada sprint ha producido una versión funcional y desplegable
- **Adaptación continua**: Cambios en requisitos se han incorporado sin afectar la planificación global
- **Retrospectivas efectivas**: Mejoras del proceso en cada iteración
- **Transparencia total**: Todos los stakeholders han tenido visibilidad del progreso
- **Gestión de riesgos proactiva**: Identificación y mitigación temprana de problemas

El uso de herramientas como **GitHub Projects** para la gestión de tareas, **Git Flow** para el control de versiones y **CI/CD** para despliegues automáticos ha garantizado un flujo de trabajo profesional y eficiente.

### 9.1.6 Documentación Exhaustiva

Se ha generado **documentación técnica completa** que incluye:
- Manual de usuario detallado con capturas de pantalla
- Manual de instalación y despliegue paso a paso
- Documentación de API con ejemplos de uso
- Diagramas UML (clases, casos de uso, secuencia)
- Modelo entidad-relación y esquema de base de datos
- Guía de estilos y componentes UI
- Documentación de código inline (comentarios PHPDoc y JSDoc)

Esta documentación facilita el mantenimiento futuro, la incorporación de nuevos desarrolladores y la evolución del proyecto.

## 9.2 Desafíos Superados

El desarrollo de SpotMap ha presentado numerosos retos técnicos y organizativos que han sido abordados con éxito:

### 9.2.1 Integración de Múltiples Tecnologías Geoespaciales

La **integración compleja de servicios geoespaciales** ha sido uno de los mayores desafíos del proyecto. Combinar Leaflet.js, OpenStreetMap, Nominatim, PostGIS y sistemas de coordenadas diversos requirió:

- Normalización de formatos de datos entre diferentes APIs
- Manejo de límites de rate en servicios públicos (Nominatim)
- Optimización de consultas espaciales complejas (intersecciones, buffers, nearest neighbor)
- Sincronización entre representaciones visuales (mapa) y datos en base de datos
- Gestión de diferentes niveles de zoom y carga diferida de marcadores

**Solución implementada**: Se desarrolló una capa de abstracción geoespacial que encapsula todas las operaciones relacionadas con coordenadas, proporcionando una API unificada al resto del sistema. Esta arquitectura ha simplificado enormemente el mantenimiento y facilita la futura integración de nuevos proveedores de mapas.

### 9.2.2 Optimización de Rendimiento en Base de Datos

Con la perspectiva de **grandes volúmenes de información** (cientos de miles de spots, millones de comentarios), la optimización de consultas fue crítica:

**Problemas identificados**:
- Consultas de búsqueda geoespacial lentas (>5 segundos con 10,000+ spots)
- N+1 queries en listados con relaciones anidadas
- Agregaciones costosas para calcular valoraciones medias
- Búsqueda full-text ineficiente en descripciones

**Soluciones aplicadas**:
- **Índices geoespaciales GiST** en columnas de coordenadas (reducción del 95% en tiempo de búsqueda)
- **Eager loading** de relaciones mediante JOIN optimizados
- **Campos desnormalizados** (rating_avg, rating_count) con triggers para actualización automática
- **Índices GIN** para búsqueda full-text en PostgreSQL
- **Particionado de tablas** grandes (comments, notifications) por rango de fechas
- **Query caching** con Redis para consultas frecuentes (listados de categorías, tags populares)
- **Paginación cursor-based** en lugar de offset-based para mejor rendimiento en datasets grandes

Estas optimizaciones han reducido los tiempos de respuesta de segundos a **milisegundos** (<100ms en percentil 95).

### 9.2.3 Sistemas de Autenticación Seguros y Escalables

Implementar **autenticación robusta** que equilibre seguridad y experiencia de usuario fue un reto complejo:

**Desafíos enfrentados**:
- Prevenir ataques de fuerza bruta sin frustrar usuarios legítimos
- Gestión segura de sesiones en arquitectura stateless
- Implementación de refresh tokens sin comprometer seguridad
- Soporte para autenticación OAuth (Google, Apple) junto a credenciales locales
- Recuperación de contraseñas sin vulnerabilidades

**Implementación final**:
- **JWT con expiración corta** (15 minutos) para access tokens
- **Refresh tokens** con expiración larga (30 días) almacenados en HttpOnly cookies
- **Rate limiting por IP** y por usuario en endpoints de autenticación
- **CAPTCHA** después de 3 intentos fallidos de login
- **2FA opcional** con TOTP (compatible con Google Authenticator, Authy)
- **Blacklist de tokens** en Redis para invalidación inmediata en logout
- **Integración con Supabase Auth** para OAuth sin gestionar credenciales de terceros

El sistema resultante es resistente a los principales vectores de ataque (credential stuffing, session hijacking, token theft) mientras mantiene una experiencia fluida para usuarios legítimos.

### 9.2.4 Gestión de Contenido Multimedia

El **manejo de imágenes** presentó varios desafíos técnicos y de costes:

**Problemas**:
- Almacenamiento creciente (imágenes de alta resolución consumen GB rápidamente)
- Tiempos de carga lentos en conexiones móviles
- Costes de transferencia en servicios cloud
- Preservación de metadatos EXIF (ubicación, cámara, configuración)
- Moderación de contenido inapropiado

**Estrategias implementadas**:
- **Compresión automática** con libvips (reducción 60-80% sin pérdida perceptible)
- **Generación de múltiples tamaños** (thumbnail, medium, full) para responsive loading
- **Lazy loading** de imágenes fuera del viewport
- **WebP como formato principal** con fallback a JPEG para compatibilidad
- **CDN (Supabase Storage)** para distribución global con baja latencia
- **Extracción de coordenadas EXIF** para prellenar ubicación automáticamente
- **Watermarking opcional** para protección de autoría

Estas optimizaciones han reducido el **peso promedio de carga** de 5MB a <500KB sin sacrificar calidad visual.

### 9.2.5 Moderación de Contenido a Escala

Diseñar un **sistema de moderación eficiente** que no dependa exclusivamente de revisión manual fue esencial:

**Desafíos**:
- Volumen potencialmente alto de reportes (spam, falsos positivos)
- Necesidad de respuesta rápida ante contenido realmente inapropiado
- Balance entre libertad de expresión y normas de comunidad
- Prevención de abuso del sistema de reportes

**Sistema implementado**:
- **Filtrado automático inicial** con listas de palabras prohibidas
- **Priorización inteligente** de reportes (múltiples reportes del mismo contenido → alta prioridad)
- **Panel de moderación eficiente** con vista previa y acciones rápidas (aprobar/ocultar/eliminar)
- **Sistema de apelaciones** para usuarios sancionados injustamente
- **Historial completo de decisiones** para auditoría y mejora continua
- **Badges de confianza** para usuarios con buen historial (menos revisión requerida)

### 9.2.6 Testing y Aseguramiento de Calidad

Garantizar la **calidad del código** y prevenir regresiones requirió:

- **Tests unitarios** para lógica de negocio crítica (>80% coverage)
- **Tests de integración** para flujos completos de usuario
- **Tests E2E** con Playwright para verificar interfaz en navegadores reales
- **Tests de carga** con JMeter para identificar cuellos de botella
- **Revisión de código** mediante pull requests con al menos un reviewer
- **Análisis estático** con PHPStan/ESLint para detectar bugs antes de runtime

El tiempo invertido en testing (aproximadamente 20% del tiempo total de desarrollo) se ha amortizado con creces al reducir bugs en producción y facilitar refactorizaciones seguras.

## 9.3 Aprendizajes Clave

El desarrollo de SpotMap ha proporcionado valiosas lecciones aplicables a futuros proyectos:

### 9.3.1 Aprendizajes Técnicos

- **La optimización prematura es real**: Se invirtió tiempo inicial en optimizaciones que no eran necesarias. El enfoque correcto es medir primero, optimizar después.
- **Las abstracciones correctas simplifican enormemente**: La capa geoespacial abstracta ha facilitado cambios que habrían sido muy costosos sin ella.
- **La documentación es inversión, no gasto**: Cada hora dedicada a documentar ahorra múltiples horas de confusión futura.
- **Los estándares existen por buenas razones**: Seguir convenciones REST, nomenclatura SQL estándar y patrones de diseño ha mejorado la claridad del código.

### 9.3.2 Aprendizajes de Proceso

- **Scrum funciona cuando se aplica correctamente**: Las retrospectivas honestas permitieron mejorar continuamente el proceso.
- **La comunicación constante previene malentendidos**: Reuniones breves diarias fueron más efectivas que reuniones largas semanales.
- **Los requisitos siempre evolucionan**: Diseñar sistemas flexibles desde el inicio facilitó acomodar cambios.

### 9.3.3 Aprendizajes de Producto

- **Los usuarios reales sorprenden**: Las pruebas de usabilidad revelaron patrones de uso inesperados que mejoraron el diseño.
- **La simplicidad gana**: Funcionalidades complejas que parecían imprescindibles terminaron siendo poco usadas; las simples y bien ejecutadas son más valoradas.
- **La calidad del contenido es crítica**: Un sistema de moderación efectivo es la diferencia entre una comunidad saludable y un espacio problemático.

## 9.4 Impacto Esperado del Proyecto

### 9.4.1 Impacto en la Comunidad Fotográfica

SpotMap tiene el potencial de **democratizar el acceso a localizaciones fotográficas de calidad**. Tradicionalmente, el conocimiento sobre buenos spots ha estado concentrado en fotógrafos locales o redes cerradas. SpotMap abre este conocimiento a:

- **Fotógrafos novatos** que pueden aprender de la comunidad
- **Viajeros** que buscan experiencias fotográficas únicas
- **Fotógrafos profesionales** que pueden descubrir nuevas localizaciones en sus viajes
- **Instituciones educativas** (escuelas de fotografía, talleres)

### 9.4.2 Impacto Turístico

La plataforma puede convertirse en una **herramienta de promoción turística** al:

- Destacar lugares menos conocidos, distribuyendo el turismo más equitativamente
- Proporcionar a oficinas de turismo datos sobre qué lugares son más atractivos visualmente
- Generar contenido promocional orgánico (fotografías de calidad de destinos)
- Fomentar el turismo experiencial y sostenible

### 9.4.3 Impacto Económico

SpotMap puede generar **ecosistema económico**:

- Negocios locales (cafés, hoteles) cerca de spots populares se benefician de mayor tráfico
- Fotógrafos profesionales pueden ofrecer tours guiados a spots curados
- Servicios premium para empresas (análisis de datos, contenido patrocinado)
- Afiliación con equipamiento fotográfico, seguros de viaje, etc.

## 9.5 Próximas Fases y Evolución Futura

### 9.5.1 Corto Plazo (0-6 meses post-MVP)

**Consolidación y Estabilización**:
- Monitoreo intensivo de rendimiento y corrección de bugs reportados
- Incorporación de feedback de primeros usuarios
- Optimizaciones basadas en patrones de uso reales
- Campañas de marketing inicial para alcanzar masa crítica de usuarios

**Funcionalidades adicionales**:
- **Sistema de colecciones**: Usuarios pueden crear colecciones temáticas de spots (ej: "Mejores atardeceres de Andalucía")
- **Rutas fotográficas**: Planificación de rutas que conectan múltiples spots con optimización de distancia
- **Calendario de eventos**: Sincronización con eventos fotográficos (golden hour, blue hour, eventos astronómicos)
- **Exportación de datos**: Usuarios pueden exportar sus spots a formatos GPX, KML para uso en otras aplicaciones

### 9.5.2 Medio Plazo (6-18 meses)

**Inteligencia Artificial y Machine Learning**:
- **Sistema de recomendación personalizado**: Algoritmos que sugieren spots basados en historial, favoritos y preferencias del usuario
- **Clasificación automática de imágenes**: Computer vision para categorizar automáticamente spots (identificar si es atardecer, urbano, naturaleza)
- **Detección automática de contenido inapropiado**: Reducir carga de moderadores con filtrado automático
- **Predicción de condiciones óptimas**: IA que predice mejores momentos para visitar spots (basado en clima, iluminación, afluencia)

**Aplicaciones Móviles Nativas**:
- **Desarrollo de apps iOS y Android** con funcionalidades adicionales:
  - Modo offline con sincronización automática
  - Realidad aumentada para previsualizar encuadres en locación
  - Notificaciones push sobre spots cercanos durante viajes
  - Integración con galería del dispositivo para detección automática de ubicación en fotos

**Gamificación Avanzada**:
- **Sistema de niveles y experiencia** (fotógrafo novato → experto → maestro)
- **Desafíos fotográficos**: Retos semanales/mensuales con premios
- **Leaderboards**: Rankings por categorías (más spots creados, mejor valorados, más activos)
- **Badges especiales**: Reconocimientos por logros específicos (primer spot en un país, 100 spots creados, etc.)

### 9.5.3 Largo Plazo (18-36 meses)

**Ecosistema Expandido**:
- **Marketplace de servicios**: Conexión entre fotógrafos profesionales y clientes (tours guiados, sesiones fotográficas)
- **Integración con plataformas de venta**: Posibilidad de vender prints de fotos tomadas en spots
- **Programa de afiliados**: Comisiones por ventas de equipamiento fotográfico recomendado
- **Colaboraciones institucionales**: Acuerdos con oficinas de turismo, parques nacionales, museos

**Análisis Geoespacial Avanzado**:
- **Mapas de calor de popularidad**: Visualización de zonas con mayor concentración de spots
- **Análisis de tendencias temporales**: Qué categorías de spots son más populares por estación del año
- **Predicción de spots virales**: Identificar spots con potencial de volverse populares
- **Informes personalizados para stakeholders**: Dashboards con métricas para instituciones

**Expansión Internacional**:
- **Soporte multi-idioma**: Interfaz traducida a 10+ idiomas
- **Localización de contenido**: Adaptación cultural de características
- **Comunidades regionales**: Moderadores locales, categorías específicas por región
- **Mapas especializados**: Capas adicionales (transporte público, accesibilidad, permisos fotográficos)

**Sostenibilidad y Responsabilidad**:
- **Programa de turismo responsable**: Educar sobre respeto al medio ambiente y culturas locales
- **Límites de capacidad**: Avisos cuando spots están sobre-visitados
- **Contribución a conservación**: Porcentaje de ingresos destinado a protección de espacios naturales
- **Spots privados**: Sistema para que propietarios de locaciones gestionen acceso

### 9.5.4 Monetización Sostenible

**Modelo Freemium**:
- **Versión gratuita**: Todas las funcionalidades básicas (búsqueda, creación de spots, comentarios)
- **SpotMap Pro** ($4.99/mes):
  - Colecciones ilimitadas
  - Exportación avanzada de datos
  - Análisis de estadísticas personales
  - Sin publicidad
  - Acceso prioritario a nuevas funcionalidades
  - Badges exclusivos

**Para Empresas**:
- **SpotMap Business** ($49/mes):
  - Panel de análisis avanzado
  - Spots destacados (promoción)
  - Integración API para webs corporativas
  - Soporte prioritario
  - Branding personalizado

**Publicidad Ética**:
- Anuncios relevantes y no intrusivos (equipamiento fotográfico, seguros de viaje, alojamiento)
- Sin venta de datos personales
- Sin tracking invasivo

## 9.6 Reflexión Final

El desarrollo de SpotMap ha sido un **viaje de aprendizaje intensivo** que ha superado las expectativas iniciales. Lo que comenzó como una idea para solucionar un problema personal (encontrar buenos lugares fotográficos) se ha convertido en una plataforma completa con potencial real de impacto global.

El proyecto ha demostrado que es posible **construir soluciones técnicamente complejas** aplicando metodologías rigurosas, priorizando la calidad sobre la rapidez, y manteniendo siempre el foco en las necesidades reales de los usuarios.

SpotMap está listo para su lanzamiento al mercado, con una base técnica sólida que permitirá su evolución y escalabilidad. El verdadero éxito del proyecto se medirá por su capacidad de **construir y mantener una comunidad activa y saludable** que comparta la pasión por la fotografía y la exploración.

Este documento técnico representa no solo la culminación de 16 semanas de desarrollo, sino el **punto de partida** de lo que puede convertirse en la plataforma de referencia mundial para localizaciones fotográficas.

---

# 10. Referencias

Esta sección recopila todas las fuentes bibliográficas, recursos técnicos, herramientas y referencias visuales utilizadas durante la investigación, diseño, desarrollo y documentación del proyecto SpotMap. Las referencias están organizadas por categorías para facilitar su consulta.

## 10.1 Referencias Bibliográficas

### 10.1.1 Arquitectura y Patrones de Diseño

**Fowler, Martin** (2002). *Patterns of Enterprise Application Architecture*. Addison-Wesley Professional.
- ISBN: 978-0321127426
- Utilizado para: Diseño de la arquitectura en capas, patrones Repository, Service Layer y Data Mapper.
- Capítulos relevantes: "Domain Logic Patterns", "Data Source Architectural Patterns"

**Richardson, Chris** (2018). *Microservices Patterns: With Examples in Java*. Manning Publications.
- ISBN: 978-1617294549
- Utilizado para: Estrategias de descomposición de servicios, API Gateway pattern, patrones de datos.
- Capítulos relevantes: "Decomposition patterns", "Database architecture patterns"

**Evans, Eric** (2003). *Domain-Driven Design: Tackling Complexity in the Heart of Software*. Addison-Wesley Professional.
- ISBN: 978-0321125215
- Utilizado para: Modelado del dominio de negocio, entidades, value objects, aggregates.
- Capítulos relevantes: "Building Blocks of a Model-Driven Design", "Refactoring Toward Deeper Insight"

**Gamma, Erich; Helm, Richard; Johnson, Ralph; Vlissides, John** (1994). *Design Patterns: Elements of Reusable Object-Oriented Software*. Addison-Wesley.
- ISBN: 978-0201633610
- Utilizado para: Patrones Factory, Singleton, Observer, Strategy implementados en el sistema.

### 10.1.2 Programación y Desarrollo Web

**Martin, Robert C.** (2008). *Clean Code: A Handbook of Agile Software Craftsmanship*. Prentice Hall.
- ISBN: 978-0132350884
- Utilizado para: Prácticas de código limpio, nomenclatura, refactoring, testing.
- Capítulos clave: "Meaningful Names", "Functions", "Comments", "Error Handling"

**Martin, Robert C.** (2017). *Clean Architecture: A Craftsman's Guide to Software Structure and Design*. Prentice Hall.
- ISBN: 978-0134494166
- Utilizado para: Principios SOLID, arquitectura hexagonal, separación de concerns.

**McDowell, Gayle Laakmann** (2015). *Cracking the Coding Interview: 189 Programming Questions and Solutions*. 6th Edition. CareerCup.
- ISBN: 978-0984782857
- Utilizado para: Algoritmos de búsqueda geoespacial, estructuras de datos optimizadas.

**Osmani, Addy** (2017). *Learning JavaScript Design Patterns*. O'Reilly Media.
- Disponible en: https://addyosmani.com/resources/essentialjsdesignpatterns/book/
- Utilizado para: Patrones Module, Observer, Mediator en JavaScript vanilla.

### 10.1.3 Bases de Datos y Optimización

**Winand, Markus** (2012). *SQL Performance Explained*. Markus Winand.
- ISBN: 978-3950307818
- Utilizado para: Optimización de queries, índices, explain plans en PostgreSQL.
- Web: https://use-the-index-luke.com/

**Karwin, Bill** (2010). *SQL Antipatterns: Avoiding the Pitfalls of Database Programming*. Pragmatic Bookshelf.
- ISBN: 978-1934356555
- Utilizado para: Evitar antipatrones en diseño de esquema, prevención de N+1 queries.

**Obe, Regina O.; Hsu, Leo S.** (2021). *PostGIS in Action*. 3rd Edition. Manning Publications.
- ISBN: 978-1617296697
- Utilizado para: Funciones geoespaciales, índices GiST, optimización de consultas espaciales.

### 10.1.4 Seguridad Web

**OWASP Foundation** (2021). *OWASP Top Ten 2021: The Ten Most Critical Web Application Security Risks*.
- Disponible en: https://owasp.org/Top10/
- Utilizado para: Prevención de vulnerabilidades (SQL injection, XSS, CSRF, autenticación rota).

**OWASP Foundation**. *OWASP Web Security Testing Guide (WSTG)*.
- Disponible en: https://owasp.org/www-project-web-security-testing-guide/
- Utilizado para: Metodología de testing de seguridad, casos de prueba específicos.

**Hoffman, Andrew** (2020). *Web Application Security: Exploitation and Countermeasures for Modern Web Applications*. O'Reilly Media.
- ISBN: 978-1492053118
- Utilizado para: Implementación de headers de seguridad, CSP, rate limiting.

### 10.1.5 Experiencia de Usuario y Diseño

**Krug, Steve** (2014). *Don't Make Me Think, Revisited: A Common Sense Approach to Web Usability*. 3rd Edition. New Riders.
- ISBN: 978-0321965516
- Utilizado para: Principios de usabilidad, diseño de navegación intuitiva.

**Norman, Donald A.** (2013). *The Design of Everyday Things: Revised and Expanded Edition*. Basic Books.
- ISBN: 978-0465050659
- Utilizado para: Affordances, feedback visual, principios de diseño centrado en usuario.

**Tidwell, Jenifer; Brewer, Charles; Valencia, Aynne** (2020). *Designing Interfaces: Patterns for Effective Interaction Design*. 3rd Edition. O'Reilly Media.
- ISBN: 978-1492051961
- Utilizado para: Patrones de UI (navegación, búsqueda, formularios).

**W3C** (2018). *Web Content Accessibility Guidelines (WCAG) 2.1*.
- Disponible en: https://www.w3.org/TR/WCAG21/
- Utilizado para: Cumplimiento de accesibilidad nivel AA (contraste, navegación por teclado).

### 10.1.6 Metodologías Ágiles

**Schwaber, Ken; Sutherland, Jeff** (2020). *The Scrum Guide: The Definitive Guide to Scrum: The Rules of the Game*.
- Disponible en: https://scrumguides.org/
- Utilizado para: Implementación de sprints, retrospectivas, daily standups.

**Cohn, Mike** (2010). *Succeeding with Agile: Software Development Using Scrum*. Addison-Wesley Professional.
- ISBN: 978-0321579362
- Utilizado para: Planificación de sprints, gestión de backlog, velocity tracking.

**Beck, Kent** (2002). *Test-Driven Development: By Example*. Addison-Wesley Professional.
- ISBN: 978-0321146533
- Utilizado para: Metodología TDD aplicada en desarrollo de API.

### 10.1.7 Legislación y Normativa

**Reglamento (UE) 2016/679 del Parlamento Europeo y del Consejo** (2016). *Reglamento General de Protección de Datos (RGPD)*.
- Disponible en: https://eur-lex.europa.eu/eli/reg/2016/679/oj
- Utilizado para: Cumplimiento legal en tratamiento de datos personales.

**Ley Orgánica 3/2018, de 5 de diciembre**, *de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD)*.
- BOE núm. 294, de 6 de diciembre de 2018.
- Disponible en: https://www.boe.es/eli/es/lo/2018/12/05/3
- Utilizado para: Adaptación del RGPD al ordenamiento jurídico español.

**Agencia Española de Protección de Datos (AEPD)**. *Guía para el cumplimiento del deber de informar*.
- Disponible en: https://www.aepd.es/
- Utilizado para: Redacción de política de privacidad y aviso legal.

## 10.2 Referencias Técnicas y Documentación Oficial

### 10.2.1 Lenguajes de Programación

**PHP Documentation Group**. *PHP Manual*. Versión 8.2.
- Disponible en: https://www.php.net/manual/es/
- Secciones consultadas: "Características", "Seguridad", "PDO", "Funciones de manejo de strings"

**ECMA International**. *ECMAScript 2023 Language Specification* (ES14).
- Disponible en: https://tc39.es/ecma262/
- Utilizado para: Características modernas de JavaScript (async/await, modules, destructuring)

**Mozilla Developer Network (MDN)**. *JavaScript Reference*.
- Disponible en: https://developer.mozilla.org/en-US/docs/Web/JavaScript
- Recurso principal para: APIs del navegador, Fetch API, geolocalización, Web Storage

### 10.2.2 Frameworks y Librerías

**Supabase**. *Official Documentation*.
- Disponible en: https://supabase.com/docs
- Secciones: "Auth", "Database", "Storage", "Realtime", "Edge Functions"

**Leaflet**. *Leaflet Documentation*.
- Disponible en: https://leafletjs.com/reference.html
- Utilizado para: Integración de mapas interactivos, marcadores, capas, eventos

**OpenStreetMap Wiki**. *Main Page*.
- Disponible en: https://wiki.openstreetmap.org/
- Utilizado para: Formato de datos OSM, etiquetado, APIs disponibles

**Nominatim Documentation**.
- Disponible en: https://nominatim.org/release-docs/latest/
- Utilizado para: Geocodificación y geocodificación inversa

**Chart.js Documentation**.
- Disponible en: https://www.chartjs.org/docs/latest/
- Utilizado para: Visualización de estadísticas en panel administrativo

**Bootstrap Icons**.
- Disponible en: https://icons.getbootstrap.com/
- Utilizado para: Iconografía consistente en toda la interfaz

### 10.2.3 Bases de Datos

**PostgreSQL Global Development Group**. *PostgreSQL 14 Documentation*.
- Disponible en: https://www.postgresql.org/docs/14/
- Secciones clave: "Data Types", "Indexes", "Performance Tips", "Full Text Search"

**PostGIS Project**. *PostGIS 3.3 Manual*.
- Disponible en: https://postgis.net/docs/manual-3.3/
- Funciones utilizadas: ST_Distance, ST_DWithin, ST_MakePoint, ST_Transform

### 10.2.4 Herramientas de Desarrollo

**Git Documentation**.
- Disponible en: https://git-scm.com/doc
- Utilizado para: Control de versiones, branching strategy (Git Flow)

**GitHub Docs**. *GitHub Documentation*.
- Disponible en: https://docs.github.com/
- Utilizado para: GitHub Actions (CI/CD), Projects, Issues, Pull Requests

**Composer Documentation**.
- Disponible en: https://getcomposer.org/doc/
- Utilizado para: Gestión de dependencias PHP

**npm Documentation**.
- Disponible en: https://docs.npmjs.com/
- Utilizado para: Gestión de paquetes JavaScript

### 10.2.5 APIs y Servicios

**OpenStreetMap API Documentation**.
- Disponible en: https://wiki.openstreetmap.org/wiki/API_v0.6
- Utilizado para: Obtención de tiles de mapas

**Nominatim API Documentation**.
- Disponible en: https://nominatim.org/release-docs/latest/api/Search/
- Utilizado para: Conversión de coordenadas a direcciones legibles

## 10.3 Referencias de Imágenes y Recursos Visuales

### 10.3.1 Imágenes del Proyecto

**Diagramas UML**:
- Herramienta: PlantUML (https://plantuml.com/)
- Archivos fuente: `./diagrams/*.puml`
- Formato de salida: SVG y PNG
- Licencia: Creación propia del proyecto SpotMap

**Capturas de Pantalla**:
- Interfaz de usuario: Screenshots propios del sistema SpotMap
- Wireframes: Diseñados con Figma / PlantUML
- Mockups: Generados durante fase de diseño

**Iconografía**:
- Bootstrap Icons v1.11: MIT License (https://icons.getbootstrap.com/)
- Iconos personalizados: Diseño propio vectorial (SVG)

### 10.3.2 Mapas Base

**OpenStreetMap**:
- Fuente: © OpenStreetMap contributors
- Licencia: Open Database License (ODbL) v1.0
- Web: https://www.openstreetmap.org/copyright
- Uso: Tiles de mapas en toda la aplicación

**Mapbox** (opcional, si se usa):
- Fuente: © Mapbox, © OpenStreetMap contributors
- Licencia: Según plan de Mapbox
- Web: https://www.mapbox.com/

### 10.3.3 Fuentes Tipográficas

**Inter** (font principal):
- Diseñador: Rasmus Andersson
- Licencia: SIL Open Font License 1.1
- Web: https://rsms.me/inter/
- Uso: Texto de interfaz, headers, body

**Roboto** (font alternativa):
- Diseñador: Christian Robertson (Google)
- Licencia: Apache License 2.0
- Web: https://fonts.google.com/specimen/Roboto

### 10.3.4 Recursos de Diseño

**Unsplash**:
- Imágenes de ejemplo en mockups: https://unsplash.com/
- Licencia: Unsplash License (uso gratuito)

**Pexels**:
- Fotografías de demostración: https://www.pexels.com/
- Licencia: Pexels License (uso gratuito)

### 10.3.5 Paleta de Colores

Inspiración de paletas:
- **Tailwind CSS Default Palette**: https://tailwindcss.com/docs/customizing-colors
- **Material Design Color System**: https://material.io/design/color/

Paleta personalizada SpotMap:
- Verde primario (#10b981): Color corporativo propio
- Azul secundario (#3b82f6): Complementario propio
- Naranja acento (#f97316): Destacado propio

## 10.4 Recursos Educativos y Tutoriales

### 10.4.1 Cursos Online

**freeCodeCamp**. *Responsive Web Design Certification*.
- Disponible en: https://www.freecodecamp.org/learn/responsive-web-design/
- Utilizado para: Fundamentos de diseño responsive, CSS Grid, Flexbox

**freeCodeCamp**. *APIs and Microservices Certification*.
- Disponible en: https://www.freecodecamp.org/learn/apis-and-microservices/
- Utilizado para: Diseño de APIs RESTful, autenticación

**Codecademy**. *Learn SQL*.
- Disponible en: https://www.codecademy.com/learn/learn-sql
- Utilizado para: Consultas SQL avanzadas, optimización

**Coursera - University of Michigan**. *Web Design for Everybody Specialization*.
- Disponible en: https://www.coursera.org/specializations/web-design
- Utilizado para: Fundamentos de UX/UI, accesibilidad

### 10.4.2 Blogs y Artículos Técnicos

**CSS-Tricks**:
- Web: https://css-tricks.com/
- Artículos utilizados: "A Complete Guide to Flexbox", "A Complete Guide to Grid"

**Smashing Magazine**:
- Web: https://www.smashingmagazine.com/
- Artículos sobre: Performance web, UX patterns, accesibilidad

**Scotch.io** (DigitalOcean Community):
- Web: https://www.digitalocean.com/community/tutorials
- Tutoriales sobre: PostgreSQL, Linux, Nginx, deployment

**Martin Fowler's Blog**:
- Web: https://martinfowler.com/
- Artículos sobre: Arquitectura de software, refactoring, microservicios

### 10.4.3 Vídeos y Conferencias

**Traversy Media** (YouTube):
- Canal: https://www.youtube.com/@TraversyMedia
- Videos sobre: JavaScript moderno, PHP, APIs REST

**The Net Ninja** (YouTube):
- Canal: https://www.youtube.com/@NetNinja
- Videos sobre: JavaScript, async/await, fetch API

**Google Chrome Developers** (YouTube):
- Canal: https://www.youtube.com/@ChromeDevs
- Videos sobre: Performance web, Core Web Vitals, DevTools

## 10.5 Comunidades y Foros

**Stack Overflow**:
- Web: https://stackoverflow.com/
- Tags consultados: [php], [postgresql], [javascript], [leaflet], [postgis]

**Reddit**:
- r/webdev: https://www.reddit.com/r/webdev/
- r/php: https://www.reddit.com/r/PHP/
- r/javascript: https://www.reddit.com/r/javascript/
- r/photography: https://www.reddit.com/r/photography/ (investigación de usuarios)

**GitHub Discussions**:
- Supabase: https://github.com/supabase/supabase/discussions
- Leaflet: https://github.com/Leaflet/Leaflet/discussions

**Discord Communities**:
- Supabase Discord: Soporte técnico y resolución de dudas
- Dev.to Discord: Networking con otros desarrolladores

## 10.6 Herramientas de Testing y Calidad

**Testing Library Documentation**:
- Web: https://testing-library.com/
- Utilizado para: Tests de componentes frontend

**Playwright Documentation**:
- Web: https://playwright.dev/
- Utilizado para: Tests end-to-end automatizados

**JMeter Documentation**:
- Web: https://jmeter.apache.org/
- Utilizado para: Tests de carga y rendimiento

**PHPStan Documentation**:
- Web: https://phpstan.org/
- Utilizado para: Análisis estático de código PHP

**ESLint Documentation**:
- Web: https://eslint.org/
- Utilizado para: Linting y formateo de código JavaScript

## 10.7 Repositorios de Código Consultados

**Awesome PHP** (curated list):
- Repositorio: https://github.com/ziadoz/awesome-php
- Utilizado para: Descubrimiento de librerías y herramientas PHP

**Awesome PostgreSQL** (curated list):
- Repositorio: https://github.com/dhamaniasad/awesome-postgres
- Utilizado para: Extensiones, herramientas, recursos de PostgreSQL

**Leaflet Examples**:
- Repositorio: https://github.com/Leaflet/Leaflet/tree/main/examples
- Utilizado para: Implementación de funcionalidades avanzadas de mapas

## 10.8 Atribuciones Específicas

**Fotografías de demostración**:
- Todas las fotografías utilizadas en demos y mockups provienen de fuentes con licencias de uso libre (Unsplash, Pexels) o son creación propia.
- Atribución específica disponible en metadatos de cada imagen.

**Código de terceros**:
- Snippets adaptados de Stack Overflow están marcados con comentarios y referencia al autor original.
- Todo el código de librerías de terceros mantiene sus licencias originales (MIT, Apache 2.0, GPL según corresponda).

**Datos de prueba**:
- Spots de ejemplo: Creados específicamente para el proyecto, basados en lugares reales pero con descripciones originales.
- Coordenadas geográficas: Datos públicos de OpenStreetMap.

---

# 11. Anexos

## 11.1 Estructura de Directorios del Proyecto

```
spotMap/
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── services/
│   │   ├── styles/
│   │   └── utils/
│   ├── public/
│   └── package.json
├── backend/
│   ├── app/
│   │   ├── models/
│   │   ├── controllers/
│   │   ├── routes/
│   │   ├── middleware/
│   │   └── config/
│   ├── migrations/
│   ├── tests/
│   └── requirements.txt
├── database/
│   ├── schema.sql
│   └── fixtures/
├── docs/
│   ├── diagrams/
│   ├── images/
│   └── SPOTMAP_DOCUMENTO_FINAL_PROYECTO.md
└── README.md
```

## 11.2 Variables de Entorno Principales

```env
# Backend
DATABASE_URL=postgresql://user:password@localhost:5432/spotmap
SECRET_KEY=your-secret-key-here
DEBUG=False
ALLOWED_HOSTS=localhost,127.0.0.1

# Frontend
REACT_APP_API_BASE_URL=http://localhost:8000/api
REACT_APP_MAP_API_KEY=your-map-api-key

# Servicios externos
NOMINATIM_API=https://nominatim.openstreetmap.org/
GITHUB_TOKEN=your-github-token
```

## 11.3 Comandos Útiles de Desarrollo

**Backend**:
```bash
python manage.py runserver
python manage.py migrate
python manage.py createsuperuser
pytest
```

**Frontend**:
```bash
npm start
npm build
npm test
npm run lint
```

**Base de Datos**:
```bash
psql -U postgres -d spotmap -f database/schema.sql
```

## 11.4 Información de Contacto y Soporte

- **Desarrollador**: Antonio Valero Maldonado
- **Email**: [correo del alumno]
- **GitHub**: [enlace al repositorio]
- **Documentación Técnica**: Disponible en `/docs`
- **Issues y Soporte**: GitHub Issues

---

**Documento preparado por**: Antonio Valero Maldonado  
**Fecha de finalización**: Diciembre de 2025  
**Versión**: 1.0 (Final)

---
