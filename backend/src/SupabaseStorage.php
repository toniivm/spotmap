<?php
namespace SpotMap;

/**
 * Wrapper mínimo para Supabase Storage (uso directo de REST).
 * Requiere bucket creado previamente (ej: 'spots').
 */
class SupabaseStorage
{
    private static function baseUrl(): string
    {
        $url = Config::get('SUPABASE_URL');
        return rtrim($url, '/') . '/storage/v1';
    }

    private static function authHeaders(): array
    {
        $service = Config::get('SUPABASE_SERVICE_KEY');
        $anon = Config::get('SUPABASE_ANON_KEY');
        $key = $service ?: $anon;
        return [
            'apikey: ' . $key,
            'Authorization: Bearer ' . $key
        ];
    }

    public static function upload(string $path, string $contents, string $mime): bool
    {
        // path: bucket/filename
        [$bucket, $object] = explode('/', $path, 2);
        $url = self::baseUrl() . '/object/' . $bucket . '/' . $object;
        $headers = array_merge(self::authHeaders(), [
            'Content-Type: ' . $mime,
            'x-upsert: false'
        ]);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contents);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    public static function publicUrl(string $path): string
    {
        [$bucket, $object] = explode('/', $path, 2);
        $base = rtrim(Config::get('SUPABASE_URL'), '/');
        return $base . '/storage/v1/object/public/' . $bucket . '/' . $object;
    }

    public static function deleteIfBucketPath(string $maybeUrl): void
    {
        // Intentar extraer bucket/objeto si URL pública
        $base = rtrim(Config::get('SUPABASE_URL'), '/');
        $prefix = $base . '/storage/v1/object/public/';
        if (strpos($maybeUrl, $prefix) !== 0) return; // no es URL pública supabase
        $relative = substr($maybeUrl, strlen($prefix)); // bucket/objeto
        $parts = explode('/', $relative, 2);
        if (count($parts) !== 2) return;
        [$bucket, $object] = $parts;
        $url = self::baseUrl() . '/object/' . $bucket . '/' . $object;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::authHeaders());
        curl_exec($ch);
        curl_close($ch);
    }
}
