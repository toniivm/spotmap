<?php
namespace SpotMap;

use SpotMap\Controllers\SpotController;

class Router
{
    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        // Normalizar prefijo /api
        if (strpos($path, '/api') === 0) {
            $path = substr($path, 4); // elimina /api
        }

        $parts = array_values(array_filter(explode('/', $path)));

        // Rutas: /spots, /spots/{id}
        if (count($parts) === 0) {
            $this->respond(200, ['status' => 'ok', 'api' => 'spotmap']);
            return;
        }

        if ($parts[0] === 'spots') {
            $controller = new SpotController();
            if ($method === 'GET' && count($parts) === 1) {
                $controller->index();
                return;
            }
            if ($method === 'POST' && count($parts) === 1) {
                $controller->store();
                return;
            }
            if ($method === 'GET' && count($parts) === 2) {
                $controller->show((int)$parts[1]);
                return;
            }
            if ($method === 'DELETE' && count($parts) === 2) {
                $controller->destroy((int)$parts[1]);
                return;
            }
        }

        $this->respond(404, ['error' => 'Route not found']);
    }

    private function respond(int $status, $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }
}
