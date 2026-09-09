<?php
/**
 * MediMax Bootstrap - Base Controller
 */
class Controller {
    
    protected $data = [];
    
    /**
     * Render a view file with data
     */
    protected function view($viewPath, $data = []) {
        // Merge controller data with view-specific data
        $data = array_merge($this->data, $data);
        
        // Extract data to make variables available in view
        extract($data);
        
        // Set current page for navigation highlighting
        $currentPage = $_GET['page'] ?? 'home';
        
        // Include the view file
        $viewFile = APP_PATH . '/views/' . $viewPath . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<h1>View not found: {$viewPath}</h1>";
        }
    }
    
    /**
     * Include a layout partial
     */
    public static function partial($partialPath, $data = []) {
        extract($data);
        $currentPage = $_GET['page'] ?? 'home';
        $file = APP_PATH . '/views/' . $partialPath . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
    
    /**
     * Include a component
     */
    public static function component($componentName, $data = []) {
        extract($data);
        $file = APP_PATH . '/views/components/' . $componentName . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
