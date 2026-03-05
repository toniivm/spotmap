<?php
namespace SpotMap;

/**
 * Constantes centralizadas del proyecto
 * Única fuente de verdad para enums y configuración
 * Sigue PROJECT_GUIDELINES: Escalabilidad y profesionalismo
 */
class Constants
{
    // Categorías permitidas para spots
    const SPOT_CATEGORIES = [
        'montaña',
        'playa',
        'naturaleza',
        'arquitectura',
        'cultural',
        'urbano',
        'gastronomía',
        'deportivo',
        'otro'
    ];

    // Estados permitidos para spots
    const SPOT_STATUS = [
        'pending',   // Esperando aprobación (usuarios normales)
        'approved',  // Aprobado (visible públicamente)
        'rejected'   // Rechazado (solo propietario puede ver)
    ];

    // Roles de usuario
    const USER_ROLES = [
        'user',       // Usuario normal (puede crear spots, votar, comentar)
        'moderator',  // Moderador (puede aprobar/rechazar spots)
        'admin'       // Admin (acceso total)
    ];

    // Rol por defecto
    const DEFAULT_ROLE = 'user';

    // Límites de validación
    const SPOT_TITLE_MAX = 255;
    const SPOT_TITLE_MIN = 1;
    const SPOT_DESCRIPTION_MAX = 1000;
    const TAG_MAX_COUNT = 10;
    const TAG_MAX_LENGTH = 50;
    const MAX_IMAGE_SIZE = 5242880; // 5MB en bytes
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    // Coordenadas válidas
    const LAT_MIN = -90;
    const LAT_MAX = 90;
    const LNG_MIN = -180;
    const LNG_MAX = 180;

    /**
     * Obtener categorias como string para errores
     */
    public static function getCategoriesString(): string
    {
        return implode(', ', self::SPOT_CATEGORIES);
    }

    /**
     * Obtener status como string para errores
     */
    public static function getStatusString(): string
    {
        return implode(', ', self::SPOT_STATUS);
    }

    /**
     * Verificar si categoría es válida
     */
    public static function isValidCategory(string $category): bool
    {
        return in_array($category, self::SPOT_CATEGORIES, true);
    }

    /**
     * Verificar si status es válido
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::SPOT_STATUS, true);
    }

    /**
     * Verificar si rol es válido
     */
    public static function isValidRole(string $role): bool
    {
        return in_array($role, self::USER_ROLES, true);
    }

    /**
     * Verificar si usuario es moderador o admin
     */
    public static function isModerator(string $role): bool
    {
        return in_array($role, ['moderator', 'admin'], true);
    }
}
