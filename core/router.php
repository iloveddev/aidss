<?php
/**
 * AID Router
 * Simple routing for API and Admin.
 */

class AID_Router {
    private $routes = [];

    public function add($path, $callback) {
        $this->routes[$path] = $callback;
    }

    public function dispatch($requestUri) {
        $path = parse_url($requestUri, PHP_URL_PATH);
        // Remove base directory if applicable
        $path = str_replace('/AID', '', $path); 
        
        if (isset($this->routes[$path])) {
            return call_user_func($this->routes[$path]);
        }
        
        http_response_code(404);
        echo "404 Not Found";
    }
}
