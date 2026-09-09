<?php
/**
 * MediMax Bootstrap - Simple Query Parameter Router
 */
class Router {
    
    private $routes = [];
    
    /**
     * Register a route
     */
    public function register($page, $controller, $method) {
        $this->routes[$page] = ['controller' => $controller, 'method' => $method];
    }
    
    /**
     * Dispatch the current request
     */
    public function dispatch() {
        $page = $_GET['page'] ?? 'home';
        
        if (isset($this->routes[$page])) {
            $route = $this->routes[$page];
            $controllerName = $route['controller'];
            $method = $route['method'];
            
            $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    
                    if (method_exists($controller, $method)) {
                        $controller->$method();
                        return;
                    }
                }
            }
        }
        
        // 404 - Page not found
        $this->show404($page);
    }
    
    /**
     * Show a Bootstrap-styled 404 page
     */
    private function show404($page) {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Page Not Found | MediMax</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>body{font-family:'Poppins',sans-serif;background:rgb(180,222,231);min-height:100vh;display:flex;align-items:center;justify-content:center;}</style>
        </head>
        <body>
            <div class="text-center">
                <h1 class="display-1 fw-bold" style="color:#26547C;">404</h1>
                <p class="fs-4 text-muted">Page not found</p>
                <p class="text-muted">The page <strong>"<?= htmlspecialchars($page) ?>"</strong> does not exist.</p>
                <a href="<?= BASE_URL ?>" class="btn btn-lg mt-3" style="background:#70ABAF;color:#fff;border-radius:10px;">
                    <i class="fas fa-home me-2"></i>Go Home
                </a>
            </div>
        </body>
        </html>
        <?php
    }
}
