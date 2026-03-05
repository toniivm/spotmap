<?php
namespace SpotMap;

/**
 * Adaptador para Supabase REST API
 * Permite usar Supabase como backend de base de datos
 */
class SupabaseClient
{
    private string $url;
    private string $key;
    private ?string $anonKey = null;
    private ?string $serviceKey = null;
    private string $table = 'spots';
    private array $extraTables = [
        'favorites' => 'favorites',
        'comments' => 'comments',
        'ratings' => 'ratings',
        'reports' => 'reports'
    ];

    public function __construct()
    {
        $this->url = Config::get('SUPABASE_URL');
        // Priorizar service key (privilegios) si existe, fallback a anon
        $service = Config::get('SUPABASE_SERVICE_KEY');
        $anon = Config::get('SUPABASE_ANON_KEY');
        $this->serviceKey = $service ?: null;
        $this->anonKey = $anon ?: null;
        $this->key = $service ?: $anon;

        if (!$this->url || !$this->key) {
            throw new \Exception('Supabase credentials not configured (SUPABASE_URL + SUPABASE_SERVICE_KEY/ANON_KEY)');
        }
    }

    /**
     * Realizar petición HTTP a Supabase REST API
     */
    private function request(string $method, string $endpoint, $body = null, ?string $userToken = null, bool $useServiceRole = false): array
    {
        $url = rtrim($this->url, '/') . '/rest/v1/' . ltrim($endpoint, '/');
        $effectiveKey = $this->anonKey ?: $this->serviceKey ?: $this->key;
        if ($useServiceRole && $this->serviceKey) {
            $effectiveKey = $this->serviceKey;
        }
        $auth = $userToken ?: $effectiveKey;
        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $auth,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new \Exception($error['message'] ?? 'Supabase API error: ' . $httpCode);
        }

        return json_decode($response, true) ?? [];
    }

    private function requestNoReturn(string $method, string $endpoint, ?string $userToken = null): int
    {
        $url = rtrim($this->url, '/') . '/rest/v1/' . ltrim($endpoint, '/');
        $auth = $userToken ?: $this->key;
        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $auth,
            'Content-Type: application/json'
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }

    /**
     * Obtener todos los spots
     */
    public function getAllSpots(int $limit = 100, int $offset = 0, array $filters = []): array
    {
        $params = [
            'select=*',
            'order=created_at.desc',
            'limit=' . $limit,
            'offset=' . $offset
        ];
        if (!empty($filters['category'])) {
            $params[] = 'category=eq.' . urlencode($filters['category']);
        }
        if (!empty($filters['tag'])) {
            // tags ilike pattern
            $params[] = 'tags=ilike.*' . urlencode($filters['tag']) . '*';
        }
        if (!empty($filters['schedule'])) {
            $params[] = 'schedule=eq.' . urlencode($filters['schedule']);
        }
        $endpoint = $this->table . '?' . implode('&', $params);
        return $this->request('GET', $endpoint);
    }

    public function listSpotsByStatus(string $status, int $limit = 100, int $offset = 0, ?string $userToken = null): array
    {
        $params = [
            'select=*',
            'status=eq.' . urlencode($status),
            'order=created_at.desc',
            'limit=' . $limit,
            'offset=' . $offset
        ];
        $endpoint = $this->table . '?' . implode('&', $params);
        return $this->request('GET', $endpoint, null, $userToken);
    }

    /**
     * Obtener un spot por ID
     */
    public function getSpot(int $id): ?array
    {
        $endpoint = "{$this->table}?id=eq.{$id}&select=*";
        $result = $this->request('GET', $endpoint);
        return $result[0] ?? null;
    }

    /* Favorites */
    public function favoriteSpot(string $userId, int $spotId, ?string $userToken = null): bool
    {
        $endpoint = $this->extraTables['favorites'];
        $this->request('POST', $endpoint, ['user_id' => $userId, 'spot_id' => $spotId], $userToken);
        return true;
    }
    public function unfavoriteSpot(string $userId, int $spotId, ?string $userToken = null): bool
    {
        $endpoint = $this->extraTables['favorites'] . "?user_id=eq.$userId&spot_id=eq.$spotId";
        $this->request('DELETE', $endpoint, null, $userToken);
        return true;
    }
    public function listFavorites(int $spotId): array
    {
        $endpoint = $this->extraTables['favorites'] . "?spot_id=eq.$spotId&select=user_id,created_at";
        return $this->request('GET', $endpoint);
    }

    /* Comments */
    public function listComments(int $spotId): array
    {
        $endpoint = $this->extraTables['comments'] . "?spot_id=eq.$spotId&select=*&order=created_at.asc";
        return $this->request('GET', $endpoint);
    }
    public function addComment(string $userId, int $spotId, string $body, ?string $userToken = null): array
    {
        $endpoint = $this->extraTables['comments'];
        $res = $this->request('POST', $endpoint, ['user_id' => $userId, 'spot_id' => $spotId, 'content' => $body], $userToken);
        return $res[0] ?? $res;
    }
    public function deleteComment(int $commentId, ?string $userToken = null): bool
    {
        $endpoint = $this->extraTables['comments'] . "?id=eq.$commentId";
        $this->request('DELETE', $endpoint, null, $userToken);
        return true;
    }

    /* Ratings */
    public function rateSpot(string $userId, int $spotId, int $score, ?string $userToken = null): array
    {
        $endpoint = $this->extraTables['ratings'];
        $res = $this->request('POST', $endpoint, ['user_id' => $userId, 'spot_id' => $spotId, 'score' => $score], $userToken);
        return $res[0] ?? $res;
    }
    public function getRatingAggregate(int $spotId): array
    {
        $endpoint = $this->extraTables['ratings'] . "?spot_id=eq.$spotId&select=score"; // fetch scores
        $ratings = $this->request('GET', $endpoint);
        $count = count($ratings);
        $avg = $count ? array_sum(array_column($ratings, 'score')) / $count : 0;
        return ['count' => $count, 'average' => round($avg, 2)];
    }

    /* Reports */
    public function reportSpot(string $userId, int $spotId, string $reason, ?string $userToken = null): array
    {
        $endpoint = $this->extraTables['reports'];
        $res = $this->request('POST', $endpoint, ['user_id' => $userId, 'spot_id' => $spotId, 'reason' => $reason], $userToken);
        return $res[0] ?? $res;
    }
    public function listReports(string $status = 'pending', ?string $userToken = null): array
    {
        $endpoint = $this->extraTables['reports'] . "?status=eq.$status&select=*";
        return $this->request('GET', $endpoint, null, $userToken);
    }
    public function moderateReport(int $reportId, string $newStatus, string $moderatorId, ?string $note = null, ?string $userToken = null): bool
    {
        $endpoint = $this->extraTables['reports'] . "?id=eq.$reportId";
        $this->request('PATCH', $endpoint, [
            'status' => $newStatus,
            'moderated_by' => $moderatorId,
            'moderated_at' => date('c'),
            'moderation_note' => $note
        ], $userToken);
        return true;
    }

    /**
     * Crear nuevo spot
     */
    public function createSpot(array $data, ?string $userToken = null): array
    {
        // Convertir tags a JSON si es array
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        $result = $this->request('POST', $this->table, $data, $userToken);
        return $result[0] ?? $result;
    }

    /**
     * Actualizar spot
     */
    public function updateSpot(int $id, array $data, ?string $userToken = null): array
    {
        $endpoint = "{$this->table}?id=eq.{$id}";
        $result = $this->request('PATCH', $endpoint, $data, $userToken);
        return $result[0] ?? $result;
    }

    /**
     * Eliminar spot
     */
    public function deleteSpot(int $id, ?string $userToken = null): bool
    {
        $endpoint = "{$this->table}?id=eq.{$id}";
        $this->request('DELETE', $endpoint, null, $userToken);
        return true;
    }

    /**
     * Contar total de spots
     */
    public function countSpots(): int
    {
        $endpoint = "{$this->table}?select=count";
        $result = $this->request('GET', $endpoint);
        return $result[0]['count'] ?? 0;
    }

    /* Popularity view */
    public function getPopularity(): array
    {
        $endpoint = 'spot_popularity?select=*';
        return $this->request('GET', $endpoint);
    }

    public function getProfileRole(string $userId, ?string $userToken = null): ?string
    {
        if ($userId === '') {
            return null;
        }
        $endpoint = 'profiles?select=role&user_id=eq.' . urlencode($userId) . '&limit=1';
        $result = $this->request('GET', $endpoint, null, $userToken);
        return $result[0]['role'] ?? null;
    }

    /* Global stats helpers */
    public function countTable(string $table): int
    {
        $endpoint = "$table?select=count";
        $res = $this->request('GET', $endpoint, null, null, true);
        return $res[0]['count'] ?? 0;
    }
    public function averageRatingAll(): float
    {
        $endpoint = 'ratings?select=score';
        $res = $this->request('GET', $endpoint, null, null, true);
        if (!$res) return 0.0;
        $scores = array_column($res, 'score');
        return $scores ? round(array_sum($scores)/count($scores),2) : 0.0;
    }

    /* User-specific listings for export */
    public function userFavorites(string $userId): array
    { return $this->request('GET', 'favorites?user_id=eq.' . $userId . '&select=spot_id,created_at'); }
    public function userComments(string $userId): array
    { return $this->request('GET', 'comments?user_id=eq.' . $userId . '&select=id,spot_id,body,created_at'); }
    public function userRatings(string $userId): array
    { return $this->request('GET', 'ratings?user_id=eq.' . $userId . '&select=spot_id,score,created_at'); }
    public function userReports(string $userId): array
    { return $this->request('GET', 'reports?user_id=eq.' . $userId . '&select=id,spot_id,reason,status,created_at'); }

    public function countReportsByStatus(string $status): int
    {
        $endpoint = 'reports?status=eq.' . $status . '&select=count';
        $res = $this->request('GET', $endpoint, null, null, true);
        return $res[0]['count'] ?? 0;
    }

    /* Bulk delete all user related rows (favorites, comments, ratings, reports) */
    public function bulkDeleteUserData(string $userId): array
    {
        $tables = ['favorites','comments','ratings','reports'];
        $deleted = [];
        foreach ($tables as $t) {
            $code = $this->requestNoReturn('DELETE', $t . '?user_id=eq.' . $userId);
            $deleted[$t] = $code;
        }
        return $deleted;
    }
}
