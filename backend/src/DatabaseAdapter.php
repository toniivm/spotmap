<?php
namespace SpotMap;

/**
 * Detecta y usa automáticamente Supabase o MySQL
 */
class DatabaseAdapter
{
    private static $useSupabase = null;
    private static $supabase = null;
    private static $hasUserIdColumn = null;
    private static $hasStatusColumn = null;
    private static $hasImagePath2Column = null;

    public static function useSupabase(): bool
    {
        if (self::$useSupabase === null) {
            $url = Config::get('SUPABASE_URL');
            $service = Config::get('SUPABASE_SERVICE_KEY');
            $anon = Config::get('SUPABASE_ANON_KEY');
            $key = $service ?: $anon;
            self::$useSupabase = !empty($url) && !empty($key);
        }
        return self::$useSupabase;
    }

    public static function getClient()
    {
        if (self::useSupabase()) {
            if (self::$supabase === null) {
                self::$supabase = new SupabaseClient();
            }
            return self::$supabase;
        }
        return Database::pdo();
    }

    public static function getAllSpots(int $limit = 100, int $offset = 0, array $filters = []): array
    {
        if (self::useSupabase()) {
            $spots = self::getClient()->getAllSpots($limit, $offset, $filters);
            $total = count($spots);
            
            // Parsear tags JSONB
            array_walk($spots, function(&$spot) {
                if (isset($spot['tags']) && is_string($spot['tags'])) {
                    $spot['tags'] = json_decode($spot['tags'], true) ?? [];
                }
            });
            
            // Popularity: if requested, enrich with aggregate popularity view (basic join by id)
            if (!empty($filters['popularity'])) {
                $pop = self::getClient()->getPopularity();
                $popIndex = [];
                foreach ($pop as $p) { $popIndex[$p['spot_id']] = $p; }
                foreach ($spots as &$s) {
                    $pid = $s['id'] ?? null;
                    if ($pid && isset($popIndex[$pid])) {
                        $s['popularity'] = $popIndex[$pid];
                    }
                }
            }
            // Distance filtering post-fetch (Haversine) if center + max_distance provided
            if (isset($filters['center_lat'], $filters['center_lng'], $filters['max_distance_km'])) {
                $clat = (float)$filters['center_lat'];
                $clng = (float)$filters['center_lng'];
                $maxD = (float)$filters['max_distance_km'];
                $spots = array_values(array_filter($spots, function($s) use ($clat,$clng,$maxD) {
                    if (!isset($s['lat'],$s['lng'])) return false;
                    $lat1 = deg2rad($clat); $lon1 = deg2rad($clng);
                    $lat2 = deg2rad((float)$s['lat']); $lon2 = deg2rad((float)$s['lng']);
                    $dlat = $lat2 - $lat1; $dlon = $lon2 - $lon1;
                    $a = sin($dlat/2)**2 + cos($lat1)*cos($lat2)*sin($dlon/2)**2;
                    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                    $distance = 6371 * $c;
                    return $distance <= $maxD;
                }));
                $total = count($spots);
            }
            return ['spots' => $spots, 'total' => $total];
        } else {
            $pdo = self::getClient();

            $where = [];
            $params = [];

            if (!empty($filters['category'])) {
                $where[] = 'category = :category';
                $params[':category'] = $filters['category'];
            }

            if (!empty($filters['tag'])) {
                $where[] = 'tags LIKE :tag';
                $params[':tag'] = '%"' . $filters['tag'] . '"%';
            }

            if (!empty($filters['status']) && self::hasStatusColumn()) {
                $where[] = 'status = :status';
                $params[':status'] = $filters['status'];
            }

            $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

            // Contar total
            $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM spots' . $whereSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $total = (int)$countStmt->fetch()['total'];

            // Obtener spots
            $stmt = $pdo->prepare('SELECT * FROM spots' . $whereSql . ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $spots = $stmt->fetchAll();

            // Normalizar nombres de campos a frontend (lat/lng)
            array_walk($spots, function(&$spot) {
                if (array_key_exists('latitude', $spot) && !array_key_exists('lat', $spot)) {
                    $spot['lat'] = (float)$spot['latitude'];
                }
                if (array_key_exists('longitude', $spot) && !array_key_exists('lng', $spot)) {
                    $spot['lng'] = (float)$spot['longitude'];
                }
                $spot['tags'] = $spot['tags'] ? json_decode($spot['tags']) : [];
            });
            
            return ['spots' => $spots, 'total' => $total]; // Filters not implemented for local DB
        }
    }

    public static function getSpotById(int $id): array
    {
        if (self::useSupabase()) {
            $spot = self::getClient()->getSpot($id);
            if (!$spot) {
                return ['error' => 'Not found'];
            }
            if (isset($spot['tags']) && is_string($spot['tags'])) {
                $spot['tags'] = json_decode($spot['tags'], true) ?? [];
            }
            return $spot;
        } else {
            $pdo = self::getClient();
            $stmt = $pdo->prepare('SELECT * FROM spots WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $spot = $stmt->fetch();
            if (!$spot) {
                return ['error' => 'Not found'];
            }
            if (array_key_exists('latitude', $spot) && !array_key_exists('lat', $spot)) {
                $spot['lat'] = (float)$spot['latitude'];
            }
            if (array_key_exists('longitude', $spot) && !array_key_exists('lng', $spot)) {
                $spot['lng'] = (float)$spot['longitude'];
            }
            $spot['tags'] = $spot['tags'] ? json_decode($spot['tags']) : [];
            return $spot;
        }
    }

    public static function listSpotsByStatus(string $status, int $limit = 100, int $offset = 0, ?string $userToken = null): array
    {
        if (!self::useSupabase()) {
            if (!self::hasStatusColumn()) {
                return ['spots' => [], 'total' => 0];
            }
            return self::getAllSpots($limit, $offset, ['status' => $status]);
        }
        try {
            $spots = self::getClient()->listSpotsByStatus($status, $limit, $offset, $userToken);
            return ['spots' => $spots, 'total' => count($spots)];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function getProfileRole(string $userId, ?string $userToken = null): ?string
    {
        if (!self::useSupabase()) {
            return null;
        }
        return self::getClient()->getProfileRole($userId, $userToken);
    }

    public static function createSpot(array $data, ?string $userToken = null): array
    {
        if (self::useSupabase()) {
            return self::getClient()->createSpot($data, $userToken);
        } else {
            $pdo = self::getClient();
            $columns = ['title', 'description', 'latitude', 'longitude', 'tags', 'category', 'image_path'];
            $holders = [':title', ':desc', ':lat', ':lng', ':tags', ':cat', ':image_path'];

            $params = [
                ':title' => $data['title'],
                ':desc' => $data['description'] ?? null,
                ':lat' => $data['lat'],
                ':lng' => $data['lng'],
                ':tags' => isset($data['tags']) ? json_encode($data['tags']) : null,
                ':cat' => $data['category'] ?? null,
                ':image_path' => $data['image_path'] ?? null,
            ];

            if (self::hasUserIdColumn() && isset($data['user_id'])) {
                $columns[] = 'user_id';
                $holders[] = ':user_id';
                $params[':user_id'] = $data['user_id'];
            }

            if (self::hasStatusColumn()) {
                $columns[] = 'status';
                $holders[] = ':status';
                $params[':status'] = $data['status'] ?? 'approved';
            }

            if (self::hasImagePath2Column() && isset($data['image_path_2'])) {
                $columns[] = 'image_path_2';
                $holders[] = ':image_path_2';
                $params[':image_path_2'] = $data['image_path_2'];
            }

            $stmt = $pdo->prepare(
                'INSERT INTO spots (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $holders) . ')'
            );
            $stmt->execute($params);
            $id = (int)$pdo->lastInsertId();
            // Asegurar que lat/lng estén presentes en respuesta
            $out = array_merge(['id' => $id], $data);
            if (!isset($out['lat']) && isset($data['latitude'])) $out['lat'] = (float)$data['latitude'];
            if (!isset($out['lng']) && isset($data['longitude'])) $out['lng'] = (float)$data['longitude'];
            return $out;
        }
    }

    private static function hasUserIdColumn(): bool
    {
        if (self::$hasUserIdColumn !== null) {
            return self::$hasUserIdColumn;
        }

        try {
            $pdo = self::getClient();
            $stmt = $pdo->query("SHOW COLUMNS FROM spots LIKE 'user_id'");
            self::$hasUserIdColumn = (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            self::$hasUserIdColumn = false;
        }

        return self::$hasUserIdColumn;
    }

    private static function hasStatusColumn(): bool
    {
        if (self::$hasStatusColumn !== null) {
            return self::$hasStatusColumn;
        }

        try {
            $pdo = self::getClient();
            $stmt = $pdo->query("SHOW COLUMNS FROM spots LIKE 'status'");
            self::$hasStatusColumn = (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            self::$hasStatusColumn = false;
        }

        return self::$hasStatusColumn;
    }

    private static function hasImagePath2Column(): bool
    {
        if (self::$hasImagePath2Column !== null) {
            return self::$hasImagePath2Column;
        }

        try {
            $pdo = self::getClient();
            $stmt = $pdo->query("SHOW COLUMNS FROM spots LIKE 'image_path_2'");
            self::$hasImagePath2Column = (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            self::$hasImagePath2Column = false;
        }

        return self::$hasImagePath2Column;
    }

    public static function updateSpot(int $id, array $data, ?string $userToken = null): array
    {
        if (self::useSupabase()) {
            $result = self::getClient()->updateSpot($id, $data, $userToken);
            if (!$result) {
                return ['error' => 'Update failed'];
            }
            return self::getSpotById($id);
        } else {
            $pdo = self::getClient();
            $fields = [];
            $params = [':id' => $id];

            if (isset($data['title'])) {
                $fields[] = 'title = :title';
                $params[':title'] = $data['title'];
            }
            if (isset($data['description'])) {
                $fields[] = 'description = :description';
                $params[':description'] = $data['description'];
            }
            if (isset($data['category'])) {
                $fields[] = 'category = :category';
                $params[':category'] = $data['category'];
            }
            if (isset($data['tags'])) {
                $fields[] = 'tags = :tags';
                $params[':tags'] = is_array($data['tags']) ? json_encode($data['tags']) : $data['tags'];
            }
            if (isset($data['image_path'])) {
                $fields[] = 'image_path = :image_path';
                $params[':image_path'] = $data['image_path'];
            }
            if (isset($data['image_path_2'])) {
                $fields[] = 'image_path_2 = :image_path_2';
                $params[':image_path_2'] = $data['image_path_2'];
            }

            if (!$fields) {
                return ['error' => 'No fields to update'];
            }

            $sql = 'UPDATE spots SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($params);
            if (!$success) {
                return ['error' => 'Update failed'];
            }
            return self::getSpotById($id);
        }
    }

    public static function deleteSpot(int $id, ?string $userToken = null): array
    {
        if (self::useSupabase()) {
            $success = self::getClient()->deleteSpot($id, $userToken);
            return $success ? ['success' => true] : ['error' => 'Delete failed'];
        } else {
            $pdo = self::getClient();
            $stmt = $pdo->prepare('DELETE FROM spots WHERE id = :id');
            $success = $stmt->execute([':id' => $id]);
            return $success ? ['success' => true] : ['error' => 'Delete failed'];
        }
    }

    /* ===== Extended Features (Supabase only) ===== */
    public static function favorite(string $userId, int $spotId, ?string $userToken = null): array
    {
        if (!self::useSupabase()) return ['error' => 'Favorites unsupported'];
        self::getClient()->favoriteSpot($userId, $spotId, $userToken);
        return ['success' => true];
    }
    public static function unfavorite(string $userId, int $spotId, ?string $userToken = null): array
    {
        if (!self::useSupabase()) return ['error' => 'Favorites unsupported'];
        self::getClient()->unfavoriteSpot($userId, $spotId, $userToken);
        return ['success' => true];
    }
    public static function favoritesOf(int $spotId): array
    {
        if (!self::useSupabase()) return [];
        return self::getClient()->listFavorites($spotId);
    }
    public static function commentsOf(int $spotId): array
    {
        if (!self::useSupabase()) return [];
        return self::getClient()->listComments($spotId);
    }
    public static function addComment(string $userId, int $spotId, string $body, ?string $userToken = null): array
    {
        if (!self::useSupabase()) return ['error' => 'Comments unsupported'];
        return self::getClient()->addComment($userId, $spotId, $body, $userToken);
    }
    public static function deleteComment(int $commentId, ?string $userToken = null): array
    {
        if (!self::useSupabase()) return ['error' => 'Comments unsupported'];
        self::getClient()->deleteComment($commentId, $userToken);
        return ['success' => true];
    }
    public static function rate(string $userId, int $spotId, int $score, ?string $userToken = null): array
    {
        if (!self::useSupabase()) return ['error' => 'Ratings unsupported'];
        return self::getClient()->rateSpot($userId, $spotId, $score, $userToken);
    }
    public static function ratingAggregate(int $spotId): array
    {
        if (!self::useSupabase()) return ['count' => 0, 'average' => 0];
        return self::getClient()->getRatingAggregate($spotId);
    }
    public static function report(string $userId, int $spotId, string $reason, ?string $userToken = null): array
    {
        if (!self::useSupabase()) return ['error' => 'Reports unsupported'];
        return self::getClient()->reportSpot($userId, $spotId, $reason, $userToken);
    }
    public static function listReports(string $status = 'pending', ?string $userToken = null): array
    {
        if (!self::useSupabase()) return [];
        return self::getClient()->listReports($status, $userToken);
    }
    public static function moderateReport(int $reportId, string $newStatus, string $moderatorId, ?string $note = null, ?string $userToken = null): array
    {
        if (!self::useSupabase()) return ['error' => 'Reports unsupported'];
        self::getClient()->moderateReport($reportId, $newStatus, $moderatorId, $note, $userToken);
        return ['success' => true];
    }
}
