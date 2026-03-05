# SpotMap - Documentación de Diagramas UML de Interfaz de Usuario

## 📋 Índice de Diagramas

Este documento contiene referencias a todos los diagramas UML relacionados con el diseño de interfaz de SpotMap.

---

## 1. Sistema de Componentes

**Archivo:** `ui_components.puml`

**Descripción:** Diagrama de clases que muestra la arquitectura de componentes reutilizables de la interfaz, incluyendo:
- Sistema de diseño base (ColorPalette, Typography, Spacing)
- Componentes reutilizables (Button, Card, Input, Badge, Modal, etc.)
- Relaciones entre componentes y tokens de diseño

**Uso:**
```bash
java -jar plantuml.jar ui_components.puml
```

**Elementos principales:**
- `ColorPalette`: Paleta de colores del sistema
- `Typography`: Sistema tipográfico
- `Spacing`: Sistema de espaciado
- `Button`, `Card`, `Input`, `Badge`, etc.: Componentes UI

---

## 2. Pantallas Principales

**Archivo:** `ui_screens.puml`

**Descripción:** Diagrama de clases que documenta las 6 pantallas principales de la aplicación:

### Pantallas incluidas:
1. **MapScreen**: Mapa interactivo con filtros y marcadores
2. **SpotDetailScreen**: Detalle completo del spot con galería y comentarios
3. **CreateSpotScreen**: Formulario de creación multi-paso
4. **EditSpotScreen**: Extensión de CreateSpot para edición
5. **UserProfileScreen**: Perfil con estadísticas y contenido del usuario
6. **ModerationScreen**: Panel de moderación de reportes
7. **AdminDashboardScreen**: Dashboard administrativo con métricas

**Uso:**
```bash
java -jar plantuml.jar ui_screens.puml
```

**Navegación entre pantallas:**
- Clic en marcador → SpotDetailScreen
- Clic en crear → CreateSpotScreen
- Usuario propietario → EditSpotScreen
- Moderador → ModerationScreen
- Admin → AdminDashboardScreen

---

## 3. Wireframes y Layouts

**Archivo:** `ui_wireframes.puml`

**Descripción:** Estructura detallada de wireframes para cada pantalla, con especificaciones pixel a pixel.

### Estructura incluida:
- **MapScreenWireframe**: Layout Desktop y Mobile del mapa
- **SpotDetailWireframe**: Estructura de detalle de spot
- **CreateSpotWireframe**: Formulario multi-paso con validaciones
- **UserProfileWireframe**: Grid de perfil (Desktop) y Stack (Mobile)
- **ModerationWireframe**: Split-view para moderación
- **AdminDashboardWireframe**: Dashboard con grid de métricas

**Uso:**
```bash
java -jar plantuml.jar ui_wireframes.puml
```

**Breakpoints:**
- XS: 0-575px (mobile)
- SM: 576-767px (mobile landscape)
- MD: 768-991px (tablet)
- LG: 992-1199px (desktop)
- XL: 1200px+ (large desktop)

---

## 4. Sistema de Diseño Completo

**Archivo:** `ui_design_system.puml`

**Descripción:** Design System completo con todos los tokens de diseño y patrones de componentes.

### Design Tokens incluidos:
- **ColorTokens**: Paleta completa de colores (#10b981, #3b82f6, etc.)
- **TypographyTokens**: Fuentes, tamaños, pesos (32px/24px/18px/16px/14px/12px)
- **SpacingTokens**: Sistema base-8 (8px, 16px, 24px, 32px)
- **ShadowTokens**: Niveles de elevación (sm, md, lg, xl, 2xl)
- **BorderTokens**: Radios y anchos (4px, 6px, 8px, 12px)
- **BreakpointTokens**: Puntos de quiebre responsive
- **AnimationTokens**: Duraciones y timing functions
- **ZIndexTokens**: Capas de apilamiento

### Component Patterns:
- ButtonPattern (primary, secondary, danger, ghost, link)
- CardPattern (default, elevated, outlined, filled)
- InputPattern (text, email, password, search, textarea)
- BadgePattern (categorías con colores)
- ModalPattern (tamaños y animaciones)
- ToastPattern (notificaciones)
- LoaderPattern (spinners y skeletons)

**Uso:**
```bash
java -jar plantuml.jar ui_design_system.puml
```

---

## 5. Mapa de Navegación

**Archivo:** `ui_navigation_map.puml`

**Descripción:** Diagrama de clases que muestra la estructura completa de navegación según roles de usuario.

### Navegación por rol:

#### Visitante (VISITOR):
- HomePage
- ExploreSpots
- SpotDetailPublic
- LoginPage / RegisterPage
- AboutPage / TermsPage / PrivacyPage

#### Usuario Registrado (REGISTERED):
- DashboardUser
- CreateSpotPage / EditSpotPage
- SpotDetailAuth (con acciones completas)
- UserProfilePage (tabs: Info, Spots, Favorites, Comments, Settings)
- NotificationsPage

#### Moderador (MODERATOR):
- ModerationPanel
- PendingReports / ReportDetail
- HiddenContent
- ModerationStats
- DecisionHistory

#### Administrador (ADMIN):
- AdminPanel / AdminDashboard
- UserManagement
- ContentManagement (Categories, Tags, Spots)
- ReportsManagement
- SystemConfig (Settings, Theme, Maintenance, Backups)
- SystemLogs (Activity, Changes, Errors)

**Uso:**
```bash
java -jar plantuml.jar ui_navigation_map.puml
```

---

## 6. Flujo de Navegación

**Archivo:** `ui_navigation_flowchart.puml`

**Descripción:** Diagrama de actividad (flowchart) que muestra el flujo completo de navegación por todos los roles.

### Flujos principales:
1. **Visitante → Registro → Usuario**
2. **Usuario → Explorar → Crear → Gestionar spots**
3. **Usuario → Perfil → Favoritos → Comentarios**
4. **Moderador → Reportes → Revisar → Acciones**
5. **Admin → Dashboard → Gestión → Configuración → Logs**

**Uso:**
```bash
java -jar plantuml.jar ui_navigation_flowchart.puml
```

**Características:**
- Fork/Join para acciones paralelas
- Decisiones condicionales por rol
- Bucles de validación
- Notificaciones y confirmaciones

---

## 7. Sitemap Jerárquico

**Archivo:** `ui_sitemap.puml`

**Descripción:** Mapa del sitio completo mostrando todas las URLs y su jerarquía.

### Estructura:
```
SpotMap
├── Inicio (/)
│   ├── Explorar (/explore)
│   │   └── Detalle Spot (/spots/:id)
│   ├── Autenticación (/auth)
│   │   ├── Login (/login)
│   │   ├── Registro (/register)
│   │   └── Recuperar (/recovery)
│   └── Información (/info)
│       ├── Sobre (/about)
│       ├── Términos (/terms)
│       ├── Privacidad (/privacy)
│       ├── FAQ (/faq)
│       └── Contacto (/contact)
├── Dashboard (/dashboard) [REGISTERED]
│   ├── Crear Spot (/spots/create)
│   ├── Perfil (/profile)
│   │   ├── Info (/profile/info)
│   │   ├── Mis Spots (/profile/spots)
│   │   ├── Favoritos (/profile/favorites)
│   │   ├── Comentarios (/profile/comments)
│   │   └── Config (/profile/settings)
│   └── Notificaciones (/notifications)
├── Moderación (/moderation) [MODERATOR]
│   ├── Pendientes (/moderation/pending)
│   ├── Detalle (/moderation/reports/:id)
│   ├── Oculto (/moderation/hidden)
│   ├── Estadísticas (/moderation/stats)
│   └── Historial (/moderation/history)
└── Admin (/admin) [ADMIN]
    ├── Dashboard (/admin/dashboard)
    ├── Usuarios (/admin/users)
    ├── Contenido (/admin/content)
    │   ├── Categorías (/admin/categories)
    │   ├── Tags (/admin/tags)
    │   ├── Spots (/admin/spots)
    │   └── Comentarios (/admin/comments)
    ├── Reportes (/admin/reports)
    ├── Config (/admin/config)
    │   ├── Global (/admin/config/global)
    │   ├── Tema (/admin/config/theme)
    │   ├── Mantenimiento (/admin/config/maintenance)
    │   ├── Backups (/admin/config/backups)
    │   └── Email (/admin/config/email)
    └── Logs (/admin/logs)
        ├── Actividad (/admin/logs/users)
        ├── Cambios (/admin/logs/admin)
        └── Errores (/admin/logs/errors)
```

**Uso:**
```bash
java -jar plantuml.jar ui_sitemap.puml
```

---

## 🎨 Paleta de Colores de Referencia

| Elemento | Color | Código Hex | Uso |
|----------|-------|------------|-----|
| Primario | Verde esmeralda | `#10b981` | Botones principales, acentos |
| Secundario | Azul cielo | `#3b82f6` | Links, botones secundarios |
| Acento | Naranja cálido | `#f97316` | Alertas, destacados |
| Fondo | Blanco | `#ffffff` | Fondo principal |
| Fondo secundario | Gris claro | `#f3f4f6` | Tarjetas, secciones alternas |
| Texto principal | Gris oscuro | `#1f2937` | Texto body |
| Texto secundario | Gris medio | `#6b7280` | Etiquetas, descripciones |
| Error | Rojo | `#ef4444` | Mensajes de error |
| Éxito | Verde | `#22c55e` | Mensajes de éxito |
| Advertencia | Amarillo | `#eab308` | Mensajes de advertencia |

---

## 📐 Tipografía de Referencia

| Elemento | Tamaño | Peso | Uso |
|----------|--------|------|-----|
| H1 | 32px (2rem) | Bold (700) | Títulos principales |
| H2 | 24px (1.5rem) | Bold (700) | Subtítulos |
| H3 | 18px (1.125rem) | Semi-Bold (600) | Secciones |
| Body | 16px (1rem) | Regular (400) | Texto normal |
| Small | 14px (0.875rem) | Regular (400) | Labels, ayuda |
| Tiny | 12px (0.75rem) | Regular (400) | Metadata |

**Fuente principal:** Inter, Roboto, -apple-system, BlinkMacSystemFont, sans-serif

---

## 📏 Sistema de Espaciado

| Nivel | Tamaño | Uso |
|-------|--------|-----|
| space-1 | 4px | Separación mínima |
| space-2 | 8px | Padding pequeño |
| space-3 | 12px | Gap pequeño |
| space-4 | 16px | Padding estándar |
| space-6 | 24px | Margen medio |
| space-8 | 32px | Margen grande |

---

## 🔄 Generación de Diagramas

### Requisitos:
- PlantUML (instalado o vía JAR)
- Java Runtime Environment

### Comandos:

**Generar todos los diagramas:**
```bash
java -jar plantuml.jar docs/diagrams/*.puml
```

**Generar un diagrama específico:**
```bash
java -jar plantuml.jar docs/diagrams/ui_components.puml
```

**Generar en formato específico:**
```bash
java -jar plantuml.jar -tpng docs/diagrams/*.puml  # PNG
java -jar plantuml.jar -tsvg docs/diagrams/*.puml  # SVG
java -jar plantuml.jar -tpdf docs/diagrams/*.puml  # PDF
```

### Salida:
Los diagramas generados se guardarán en el mismo directorio con las extensiones `.png`, `.svg` o `.pdf`.

---

## 📱 Responsive Design

Todos los wireframes y layouts están diseñados con enfoque **mobile-first**:

1. **Mobile (XS/SM)**: 0-767px
   - Stack vertical
   - Menús colapsables
   - Botones full-width

2. **Tablet (MD)**: 768-991px
   - Grid 2 columnas
   - Sidebar colapsable
   - Componentes adaptables

3. **Desktop (LG/XL)**: 992px+
   - Grid completo (3-4 columnas)
   - Sidebar fija
   - Componentes expandidos

---

## 🔗 Relaciones entre Diagramas

```
ui_design_system.puml
    ↓ (define tokens y patrones)
ui_components.puml
    ↓ (usa componentes)
ui_screens.puml
    ↓ (define pantallas)
ui_wireframes.puml
    ↓ (estructura layouts)
ui_navigation_map.puml
    ↓ (conecta pantallas)
ui_navigation_flowchart.puml
    ↓ (flujos de usuario)
ui_sitemap.puml
    (jerarquía completa)
```

---

## 📝 Notas de Implementación

### Prioridades de desarrollo:
1. ✅ Sistema de diseño (tokens y componentes base)
2. ✅ Pantallas públicas (Mapa, Explorar, Detalle)
3. ✅ Autenticación (Login, Registro)
4. ✅ Funcionalidades de usuario (Crear, Perfil, Favoritos)
5. 🔄 Panel de moderación
6. 🔄 Panel de administración

### Tecnologías recomendadas:
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla o Vue.js)
- **Mapa**: Leaflet.js + OpenStreetMap
- **Componentes**: Sistema de diseño custom basado en tokens
- **Responsive**: CSS Grid + Flexbox + Media Queries
- **Iconos**: Font Awesome o Heroicons
- **Gráficos**: Chart.js (para admin dashboard)

---

## 📚 Referencias Adicionales

- **Figma del proyecto**: [enlace si existe]
- **Guía de estilo completa**: `docs/SPOTMAP_DOCUMENTO_FINAL_PROYECTO.md`
- **Documentación de API**: `backend/public/api.php`
- **Base de datos**: `SQL_FEATURES_SUPABASE.sql`

---

## ✅ Checklist de Implementación

### Componentes Base:
- [ ] ColorPalette system
- [ ] Typography system
- [ ] Spacing system
- [ ] Button component (todas las variantes)
- [ ] Card component
- [ ] Input component (todos los tipos)
- [ ] Badge component
- [ ] Modal component
- [ ] Toast/Notification component
- [ ] Loader/Spinner component

### Pantallas:
- [ ] MapScreen (Desktop + Mobile)
- [ ] SpotDetailScreen
- [ ] CreateSpotScreen
- [ ] EditSpotScreen
- [ ] UserProfileScreen
- [ ] ModerationScreen
- [ ] AdminDashboardScreen

### Navegación:
- [ ] Router implementation
- [ ] Breadcrumb navigation
- [ ] Role-based access control
- [ ] 404 Not Found page
- [ ] 403 Forbidden page

---

**Última actualización:** Diciembre 5, 2025  
**Versión:** 1.0  
**Autor:** SpotMap Development Team
