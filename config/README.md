# Config - Directorio de Credenciales

**⚠️ IMPORTANTE: Este directorio NO debe subirse a Git**

## Propósito
Almacenar archivos de configuración sensibles y credenciales que no deben ser públicas.

## Archivos que van aquí
- `credentials.json` - Credenciales generales del proyecto
- `*.key` - Claves privadas
- `*.pem` - Certificados
- Cualquier archivo con tokens, passwords, API keys, etc.

## Seguridad
✅ Ya está en `.gitignore` - No se subirá accidentalmente
✅ Solo debe estar en tu máquina local
✅ No compartas estos archivos por email, chat ni repositorios públicos

## Para usar en otro PC
Copia manualmente este directorio `config/` a tu otro ordenador (USB, drive privado, etc.)

