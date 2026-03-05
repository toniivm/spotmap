<?php
/**
 * Security Audit Report for SpotMap
 * Comprehensive security checklist and recommendations
 */

namespace SpotMap;

class SecurityAudit
{
    /**
     * CHECKLIST DE SEGURIDAD - SpotMap 2026
     */
    public static function getSecurityChecklist(): array
    {
        return [
            // ============================================
            // 1. AUTENTICACIÓN Y AUTORIZACIÓN
            // ============================================
            'authentication' => [
                'name' => 'Autenticación & Autorización',
                'items' => [
                    [
                        'check' => 'Password Hashing',
                        'status' => 'IMPLEMENTED',
                        'details' => 'bcrypt with $2y$ (Blowfish) - 10 rounds min',
                        'recommendation' => 'Mantener. Considerar aumentar a 12+ rounds en producción'
                    ],
                    [
                        'check' => 'OAuth2 Social Login',
                        'status' => 'IMPLEMENTED',
                        'details' => 'Google, Facebook, Twitter/X, Instagram',
                        'recommendation' => 'Usar HTTPS en callbacks, validar state parameter'
                    ],
                    [
                        'check' => 'Session Management',
                        'status' => 'PARTIAL',
                        'details' => 'JWT tokens en localStorage',
                        'recommendation' => 'Implementar refresh tokens, usar httpOnly cookies'
                    ],
                    [
                        'check' => 'Rate Limiting',
                        'status' => 'IMPLEMENTED',
                        'details' => 'RateLimiter class - 100 req/min por IP',
                        'recommendation' => 'Aumentar límites por tier de usuario'
                    ],
                ]
            ],
            
            // ============================================
            // 2. PROTECCIÓN CONTRA INYECCIONES
            // ============================================
            'injection_prevention' => [
                'name' => 'Prevención de Inyecciones',
                'items' => [
                    [
                        'check' => 'SQL Injection',
                        'status' => 'PROTECTED',
                        'details' => 'Prepared statements con PDO + parameterized queries',
                        'recommendation' => 'Auditar todos los queries, evitar string concatenation'
                    ],
                    [
                        'check' => 'XSS Protection',
                        'status' => 'PROTECTED',
                        'details' => 'htmlspecialchars() in output, CSP headers, sanitization',
                        'recommendation' => 'Implement DOMPurify on frontend for user content'
                    ],
                    [
                        'check' => 'CSRF Protection',
                        'status' => 'IMPLEMENTED',
                        'details' => 'Nonce generation y validation en Security class',
                        'recommendation' => 'Implementar SameSite cookies attribute'
                    ],
                ]
            ],
            
            // ============================================
            // 3. DATOS SENSIBLES
            // ============================================
            'sensitive_data' => [
                'name' => 'Protección de Datos Sensibles',
                'items' => [
                    [
                        'check' => 'Environment Variables',
                        'status' => 'IMPLEMENTED',
                        'details' => '.env file gitignored, no hardcoded secrets',
                        'recommendation' => 'Usar AWS Secrets Manager o HashiCorp Vault en producción'
                    ],
                    [
                        'check' => 'Password Storage',
                        'status' => 'SECURE',
                        'details' => 'bcrypt hashing, never logged',
                        'recommendation' => 'Mantener as-is'
                    ],
                    [
                        'check' => 'API Tokens',
                        'status' => 'PARTIAL',
                        'details' => 'JWT tokens en localStorage',
                        'recommendation' => 'Usar httpOnly cookies + CSRF protection'
                    ],
                    [
                        'check' => 'Logging Sanitization',
                        'status' => 'IMPLEMENTED',
                        'details' => 'AdvancedLogger filtra passwords, tokens, PII',
                        'recommendation' => 'Revisar filtros regularmente'
                    ],
                ]
            ],
            
            // ============================================
            // 4. COMUNICACIÓN Y TRANSPORTE
            // ============================================
            'transport' => [
                'name' => 'Seguridad en Transporte',
                'items' => [
                    [
                        'check' => 'HTTPS/TLS',
                        'status' => 'REQUIRED',
                        'details' => 'Enforce HTTPS en producción',
                        'recommendation' => 'TLS 1.2+ con ciphers modernos, HSTS header'
                    ],
                    [
                        'check' => 'CORS',
                        'status' => 'IMPLEMENTED',
                        'details' => 'Configurable allowedOrigin',
                        'recommendation' => 'Whitelist específico de dominios, no usar *'
                    ],
                    [
                        'check' => 'Content Security Policy',
                        'status' => 'IMPLEMENTED',
                        'details' => 'CSP headers con nonce dinámico',
                        'recommendation' => 'Auditar policy, usar report-uri para monitoring'
                    ],
                ]
            ],
            
            // ============================================
            // 5. PRIVACIDAD Y CUMPLIMIENTO
            // ============================================
            'privacy' => [
                'name' => 'Privacidad & Cumplimiento',
                'items' => [
                    [
                        'check' => 'GDPR Compliance',
                        'status' => 'PARTIAL',
                        'details' => 'Política de privacidad implementada',
                        'recommendation' => 'Implementar: derecho al olvido, consentimiento explícito, data export'
                    ],
                    [
                        'check' => 'User Consent',
                        'status' => 'PARTIAL',
                        'details' => 'Cookie consent banner missing',
                        'recommendation' => 'Agregar cookie consent con optin/optout explícito'
                    ],
                    [
                        'check' => 'Data Retention',
                        'status' => 'NOT_IMPLEMENTED',
                        'details' => 'Sin política de retención',
                        'recommendation' => 'Implementar data retention policy, auditar logs'
                    ],
                    [
                        'check' => 'Encryption at Rest',
                        'status' => 'PARTIAL',
                        'details' => 'Base de datos sin encriptación',
                        'recommendation' => 'Usar AES-256 para datos sensibles, database encryption'
                    ],
                ]
            ],
            
            // ============================================
            // 6. CONTROL DE ACCESO
            // ============================================
            'access_control' => [
                'name' => 'Control de Acceso',
                'items' => [
                    [
                        'check' => 'Role-Based Access Control (RBAC)',
                        'status' => 'IMPLEMENTED',
                        'details' => 'Roles: user, moderator, admin',
                        'recommendation' => 'Auditar endpoints para permisos correctos'
                    ],
                    [
                        'check' => 'Principle of Least Privilege',
                        'status' => 'PARTIAL',
                        'details' => 'Roles básicos pero sin granularidad',
                        'recommendation' => 'Considerar permisos por recurso'
                    ],
                    [
                        'check' => 'API Key Management',
                        'status' => 'IMPLEMENTED',
                        'details' => 'ADMIN_API_TOKEN para CLI tools',
                        'recommendation' => 'Rotar keys regularmente, usar diferentes por env'
                    ],
                ]
            ],
            
            // ============================================
            // 7. MONITOREO Y AUDITORÍA
            // ============================================
            'monitoring' => [
                'name' => 'Monitoreo & Auditoría',
                'items' => [
                    [
                        'check' => 'Activity Logging',
                        'status' => 'IMPLEMENTED',
                        'details' => 'AdvancedLogger y activity_logs table',
                        'recommendation' => 'Aumentar granularidad, implementar alertas'
                    ],
                    [
                        'check' => 'Error Handling',
                        'status' => 'IMPLEMENTED',
                        'details' => 'ErrorTracker con capturing automático',
                        'recommendation' => 'Ocultar detalles técnicos en producción'
                    ],
                    [
                        'check' => 'Security Monitoring',
                        'status' => 'PARTIAL',
                        'details' => 'Logs de intentos fallidos',
                        'recommendation' => 'Implementar detección de patrones anómalos'
                    ],
                    [
                        'check' => 'Alerting',
                        'status' => 'IMPLEMENTED',
                        'details' => 'Email alerts para eventos críticos',
                        'recommendation' => 'Escalar a PagerDuty o similar en producción'
                    ],
                ]
            ],
            
            // ============================================
            // 8. INFRAESTRUCTURA
            // ============================================
            'infrastructure' => [
                'name' => 'Seguridad de Infraestructura',
                'items' => [
                    [
                        'check' => 'Dependency Management',
                        'status' => 'IMPLEMENTED',
                        'details' => 'Composer para PHP, npm para frontend',
                        'recommendation' => 'Usar `composer audit` y `npm audit` regularmente'
                    ],
                    [
                        'check' => 'Secrets Management',
                        'status' => 'PARTIAL',
                        'details' => '.env files, OAuth client secrets',
                        'recommendation' => 'Usar vault en producción, rotación de secrets'
                    ],
                    [
                        'check' => 'Docker Security',
                        'status' => 'IMPLEMENTED',
                        'details' => 'Dockerfile con best practices',
                        'recommendation' => 'Escanear con Trivy, usar distroless images'
                    ],
                ]
            ],
        ];
    }

    /**
     * Obtener recomendaciones de implementación inmediata
     */
    public static function getImmediateActions(): array
    {
        return [
            'CRITICAL' => [
                '1. Implementar httpOnly cookies para auth tokens',
                '2. Agregar SameSite attribute a cookies',
                '3. Validar todos los permisos de endpoint',
                '4. Implementar Web Application Firewall (WAF)',
            ],
            'HIGH' => [
                '5. GDPR: Implementar derecho al olvido',
                '6. Agregar cookie consent banner',
                '7. Implementar rate limiting más granular',
                '8. Auditar todas las dependencias con vulnerabilidades',
            ],
            'MEDIUM' => [
                '9. Mejorar logging de seguridad',
                '10. Implementar encryption at rest',
                '11. Agregar 2FA optional para usuarios',
                '12. Implementar email verification',
            ],
        ];
    }
}
