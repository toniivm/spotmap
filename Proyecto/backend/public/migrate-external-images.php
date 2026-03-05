<?php
/**
 * Migrate external image URLs to Supabase Storage (spot-images bucket).
 *
 * Usage (browser):
 *   http://localhost/https-github.com-antonio-valero-daw2personal/Proyecto/spotMap/backend/public/migrate-external-images.php
 */

require_once __DIR__ . '/../src/Config.php';

use SpotMap\Config;

header('Content-Type: application/json; charset=utf-8');

Config::load();

$supabaseUrl = Config::get('SUPABASE_URL');
$serviceKey = Config::get('SUPABASE_SERVICE_KEY');

if (!$supabaseUrl || !$serviceKey) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Missing SUPABASE_URL or SUPABASE_SERVICE_KEY in backend/.env'
    ]);
    exit;
}

$bucket = 'spot-images';
$publicBase = rtrim($supabaseUrl, '/') . '/storage/v1/object/public/' . $bucket . '/';

function isExternalUrl(?string $url, string $publicBase): bool
{
    if (!$url) return false;
    if (stripos($url, 'http') !== 0) return false;
    if (stripos($url, $publicBase) === 0) return false;
    return true;
}

function fetchSpots(string $supabaseUrl, string $serviceKey): array
{
    $endpoint = rtrim($supabaseUrl, '/') . '/rest/v1/spots?select=id,image_path,image_path_2';
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code >= 400) {
        throw new Exception('Supabase REST error: ' . $code . ' - ' . $response);
    }

    return json_decode($response, true) ?: [];
}

function downloadImage(string $url): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SpotMapImageMigrator/1.0');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: image/*'
    ]);
    $data = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($code >= 400 || $data === false) {
        throw new Exception('Download failed: ' . ($err ?: ('HTTP ' . $code)));
    }

    return ['data' => $data, 'mime' => $contentType];
}

function mimeToExt(string $mime): string
{
    $mime = strtolower(trim(explode(';', $mime)[0]));
    switch ($mime) {
        case 'image/png': return 'png';
        case 'image/webp': return 'webp';
        case 'image/gif': return 'gif';
        case 'image/jpeg':
        case 'image/jpg':
        default: return 'jpg';
    }
}

function uploadToStorage(string $supabaseUrl, string $serviceKey, string $bucket, string $path, string $mime, string $data): void
{
    $endpoint = rtrim($supabaseUrl, '/') . '/storage/v1/object/' . $bucket . '/' . $path;
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
        'Content-Type: ' . $mime,
        'x-upsert: true'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($code >= 400) {
        throw new Exception('Upload failed: ' . ($err ?: ('HTTP ' . $code . ' - ' . $response)));
    }
}

function updateSpot(string $supabaseUrl, string $serviceKey, int $id, array $payload): void
{
    $endpoint = rtrim($supabaseUrl, '/') . '/rest/v1/spots?id=eq.' . $id;
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($code >= 400) {
        throw new Exception('Update failed: ' . ($err ?: ('HTTP ' . $code . ' - ' . $response)));
    }
}

try {
    $spots = fetchSpots($supabaseUrl, $serviceKey);
    $migrated = [];
    $skipped = 0;
    $failed = [];

    foreach ($spots as $spot) {
        $id = (int)($spot['id'] ?? 0);
        if ($id <= 0) continue;

        $updates = [];

        // image_path
        $imagePath = $spot['image_path'] ?? null;
        if (isExternalUrl($imagePath, $publicBase)) {
            try {
                $download = downloadImage($imagePath);
                $ext = mimeToExt($download['mime']);
                $path = 'spot-' . $id . '-img1-imported-' . time() . '.' . $ext;
                uploadToStorage($supabaseUrl, $serviceKey, $bucket, $path, $download['mime'], $download['data']);
                $updates['image_path'] = $publicBase . $path;
            } catch (Throwable $e) {
                $failed[] = [
                    'id' => $id,
                    'field' => 'image_path',
                    'url' => $imagePath,
                    'error' => $e->getMessage()
                ];
            }
        }

        // image_path_2
        $imagePath2 = $spot['image_path_2'] ?? null;
        if (isExternalUrl($imagePath2, $publicBase)) {
            try {
                $download = downloadImage($imagePath2);
                $ext = mimeToExt($download['mime']);
                $path = 'spot-' . $id . '-img2-imported-' . time() . '.' . $ext;
                uploadToStorage($supabaseUrl, $serviceKey, $bucket, $path, $download['mime'], $download['data']);
                $updates['image_path_2'] = $publicBase . $path;
            } catch (Throwable $e) {
                $failed[] = [
                    'id' => $id,
                    'field' => 'image_path_2',
                    'url' => $imagePath2,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (!empty($updates)) {
            updateSpot($supabaseUrl, $serviceKey, $id, $updates);
            $migrated[] = [
                'id' => $id,
                'updates' => $updates
            ];
        } else {
            $skipped++;
        }
    }

    echo json_encode([
        'success' => true,
        'total_spots' => count($spots),
        'migrated' => count($migrated),
        'skipped' => $skipped,
        'failed' => $failed,
        'details' => $migrated
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
