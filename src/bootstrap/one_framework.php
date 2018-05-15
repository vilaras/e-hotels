<?php
namespace OnePHP;

const ENV_DEV = 0;
const ENV_PROD = 1;
const APP_NAME = ''; //add front controller to URL (change only if you know what are you doing)

/**
 * Class CoreFramework
 * This is the main components you need for your own microframework for web 2.0
 * OneFramework extends this Class
 * Contribute in:
 * https://github.com/juliomatcom/one-php-microframework
 * Contact: juliomatcom@yandex.com
 * @author Julio Cesar Martin
 * @version 0.2.1

 */
abstract class CoreFramework{
    //Main request Object
    protected $request;
    //routes Array like routes['REQUEST_METHOD'] = array(ObjRoute1,ObjRoute2...)
    protected $routes = array();

    public function __construct(){
        $this->request = Request::createFromGlobals();
    }

    //Most popular HTTP Methods
    public abstract function get($uri, callable $callback);
    public abstract function post($uri, callable $callback);
    public abstract function put($uri, callable $callback);
    public abstract function delete($uri, callable $callback);

    /**
     * Start listen for requests
     */
    public abstract  function listen();

    /**
     * Get request Object
     * @return Request class
     */
    public function getRequest(){
        return $this->request;
    }

    /**
     * Set response HTTP Status Code
     * @param int $status default: OK 200
     * @return int status code sent
     */
    public function setStatusCode($status = 200){
        if ($status != 200){
            if (!function_exists('http_response_code'))//PHP < 5.4
            {//send header
                header('X-PHP-Response-Code: '.$status, true, $status);
            }
            else http_response_code($status);
        }
        return $status;
    }

    /**
     *********** CORE FUNCTIONS ***********
     */

    /**
     * Traverse the routes and match the request, execute the callback
     * @param string $method REQUEST_METHOD
     * @param array $routes Routes Objects
     * @param array $slugs Add any Slugs value found in the Request path (order matters)
     * @return bool true if Route was found and callback executed, false otherwise
     */
    protected function traverseRoutes($method = 'GET', array $routes, array &$slugs){
        if (isset($routes[$method])){
            foreach($routes[$method] as $route)
                if($func = $this->processUri($route, $slugs)){
                    //call callback function with params in slugs
                    call_user_func_array($func, $slugs);
                    return true;
                }
        }
        return false;
    }

    protected  function getSegment($segment_number){
        $uri = $this->request->getRequestedUri();
        $uri_segments = preg_split('/[\/]+/',$uri,null,PREG_SPLIT_NO_EMPTY);

        return isset($uri_segments[$segment_number]) ? $uri_segments[$segment_number] : false;
    }

    private function processUri($route, &$slugs = array()){
        $url =$this->request->getRequestedUri();
        $uri = parse_url($url, PHP_URL_PATH);
        $func = $this->matchUriWithRoute($uri, $route, $slugs);
        return $func ? $func : false;
    }

    static function matchUriWithRoute($uri, $route, &$slugs){
        $uri_segments = preg_split('/[\/]+/', $uri, null, PREG_SPLIT_NO_EMPTY);

        $route_segments = preg_split('/[\/]+/', $route->route, null, PREG_SPLIT_NO_EMPTY);

        if (CoreFramework::compareSegments($uri_segments, $route_segments, $slugs)){
            //route matched
            return $route->function; //Object route
        }
        return false;
    }

    /**  Match 2 uris
     * @param $uri_segments
     * @param $route_segments
     * @return bool
     */
    static function CompareSegments($uri_segments, $route_segments, &$slugs){

        if (count($uri_segments) != count($route_segments)) return false;

        foreach($uri_segments as $segment_index => $segment){
            $segment_route = $route_segments[$segment_index];
            //different segments must be an {slug} | :slug
            $is_slug = preg_match('/^{[^\/]*}$/', $segment_route) || preg_match('/^:[^\/]*/', $segment_route,$matches);

            if ($is_slug){//Note php does not support named parameters
                if (strlen(trim($segment)) === 0){
                  return false;
                }
                if(is_numeric($segment)) {
                    $segment = $segment + 0;
                }
                $slugs[ str_ireplace(array(':', '{', '}'), '', $segment_route) ] = $segment;//save slug key => value
              }
            else if($segment_route !== $segment && $is_slug !== 1)
             return false;
        }
        //match with every segment
        return true;
    }
}

/**
 * Class App
 * One PHP MVC Micro Framework
 * Contact: juliomatcom@yandex.com
 * Twitter @juliomatcom
 * Contribute to the project in Github
 * https://github.com/juliomatcom/one-php-microframework
 *
 * Controllers must be in APP_DIR/controllers
 * Views must be in APP_DIR/views
 * Assets must be in APP_DIR/public/
 *
 * @version 0.6.0
 * @author Julio Cesar Martin
 */
class App extends CoreFramework{
    //instances vars and predefined configs
    protected $db;
    protected $prod = false;

    /**
     * Initialize Framework Core
     * @param bool $prod Enviroment, set to false for Enable Debugging
     */
    public function __construct($prod = false){
        parent::__construct();

        define('APP_DIR', $this->getRootDir() .'/../'); //if your project is in src/ like in documentation, if not correct this
        define('VIEW_DIR', APP_DIR .'views/');
        define('CONTROLLER_DIR', APP_DIR .'controllers/');
        define('VIEWS_LAYOUT_ROUTE', APP_DIR .'views/layouts/');
        define('VIEWS_ROUTE', APP_DIR .'views/');//deprecated since 0.4
        define('CONTROLLERS_ROUTE', APP_DIR .'controllers/');//deprecated since 0.4

        $this->setEnvironment($prod);
    }

    /*
     * Get framework Directory
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * Change environment to prod or not
     * @param $prod bool
     */
    public function  setEnvironment($prod = false){
        $this->prod = $prod ? ENV_PROD : ENV_DEV;
    }

    /**
     * Process the request and Return a Response
     * @param $uri string for the Route example: /book/{number}/edit
     * @param callable $function executable
     */
    public function get($uri,callable $callback){
        //save route and function
        $this->routes['GET'][] = new Route($uri, $callback);
    }

    /**
     * Process a POST Request
     * @param $uri string
     * @param callable $function executable
     */
    public function post($uri,callable $callback){
        $this->routes['POST'][] = new Route($uri, $callback);
    }

    /**
     * Process a PUT Request
     * @param $uri string
     * @param callable $function executable
     */
    public function put($uri,callable $callback){
        $this->routes['PUT'][] = new Route($uri, $callback);
    }

    /**
     * Process a DELETE Request
     * @param $uri string
     * @param callable $function executable
     */
    public function delete($uri,callable $callback){
        $this->routes['DELETE'][] = new Route($uri, $callback);
    }

    /**
     * Process all Request
     * @param $uri string
     * @param callable $function executable
     */
    public function respond(callable $callback){
        $this->routes['respond'] = new Route('', $callback);
    }

    /**
     * Look for match request in routes, execute the callback function
     */
    public function listen(){
        $slugs = array();

        $run = $this->traverseRoutes($this->request->getMethod(), $this->routes, $slugs);

        if(!$run && (!isset($this->routes['respond']) || empty($this->routes['respond']))){
            return $this->error("Route not found for Path: '{$this->request->getRequestedUri()}' with HTTP Method: '{$this->request->getMethod()}'. ", 1 );
        }
        else if(!$run){ //respond for all request;
            $callback = $this->routes['respond']->function;
            $callback();
        }
        return true;
    }

    public function getRoutes(){
        return $this->routes;
    }
    /**
     * Create a route to the app
     * @param $uri
     * @return string new URL
     */
    public function generateRoute($uri){
        return (APP_NAME != '') ?('/'.APP_NAME.$uri) : $uri;
    }

    public function getRoute($uri){//deprecated since 0.5
        return $this->generateRoute($uri);
    }

    /**
     * Get current enviroment
     * @return bool True if Production is ON
     */
    public function getEnvironment(){
        return $this->prod ? ENV_PROD : ENV_DEV;
    }

    public function Redirect($href){
        echo header('Location: '.$href);
    }

    /**
     * Return HTTP Response from view.
     * @param string $filename src or content
     * @param array $vars Data to pass to the View
     * @param int $status Set the response status code.
     * @param array $headers Set response headers.
     * @param int $asText Echo as text
     */
    public function Response($filename = '', array $vars = array(), $status = 200, array $headers = array(),$asText = 0){
        $this->setStatusCode($status);

        if (count($headers)){//add extra headers
            $this->addCustomHeaders($headers);
        }
        //pass to the view
        if (!$asText){
            if(isset($vars['_layout'])) {
                $view = new View(VIEWS_ROUTE.$filename, $vars, $this, VIEWS_LAYOUT_ROUTE.$vars['_layout']);
            } else {
                $view = new View(VIEWS_ROUTE.$filename, $vars, $this);
            }
            $view->load();
        }
        else echo $filename;
    }

    public function ResponseHTML($html = '', $status = 200, array $headers = array()){
        return $this->Response($html, array(), $status, $headers, true);
    }

    /**
     * Send json encoded Response
     * @param mixed $data
     * @param int $status
     * @param array $headers
     */
    public function JsonResponse($data = null, $status = 200, array $headers = array() ){
        $this->setStatusCode($status);

        header('Content-Type: application/json');//set content type to Json
        if (count($headers)){//add extra headers
            $this->addCustomHeaders($headers);
        }

        echo json_encode($data);
    }


    private function addCustomHeaders(array $headers = array()){
        foreach($headers as $key=>$header){
            header($key.': '.$header);
        }
    }

    /**
     * Show framework's errors
     * @param string $msg
     * @param int $number
     */
    public function error($msg = '', $number = 0){
        $status = 500;
        switch ($number){
            case 1://not found
                $status = $this->setStatusCode(404);
                break;
            default://internal server error code
                $this->setStatusCode(500);
                break;
        }

        if ($this->getEnvironment() == ENV_PROD){
            echo "<div style=\"padding-top: 10px; padding-left: 10px;\">
                      <h2>:( </h2>
                      <h3>Sorry there is a problem with this request.</h3>
                      <p>The server returned <strong>$status</strong> status code.</p>
                      <p>Please try later or contact us.</p>
                 </div> ";
        }
        else if ($this->getEnvironment() == ENV_DEV){//debug enable
            echo" <div style=\"padding-top: 10px; padding-left: 10px;\">
                      <h2>Error</h2>
                      <p>The server returned <strong>$status</strong> status code.</p>
                      <p>$msg</p>";

            switch($number){
                case 1:
                    echo "
                    <p> <b>Note</b>: Routes begin always with '/' character.</p>";
                    break;
                default:
                    $this->setStatusCode(500);
                    break;
            }

            echo "  <h4>Exception:</h4>";
            throw new \Exception($msg);
            echo "</div>";
        }
        else{
            // Here your custom environments
        }
        return false;
    }
}

/**
 * Class Route
 * Formed by a string with the path and one callback function
 * @author Julio Cesar Martin
 */
class Route{
    public $route;
    public $function;

    /**
     * @param string $routeKey like /books/{id}/edit
     * @param callable $func Function
     */
    public function __construct($routeKey = '', callable $func){
        $this->route = $routeKey;
        $this->function = $func;
    }
}

/**
 * Class Request
 * Manage request params
 * @author Julio Cesar Martin
 */
class Request{
    private $get;
    private $post;
    private $files;
    private $server;
    private $cookie;
    private $method;
    private $requested_uri;
    private $body = null;

    public function __construct(array $GET = array(), array $POST = array(), array $FILES = array(), array $SERVER = array(), array $COOKIE = array()){
        $this->get = $GET;
        $this->post = $POST;
        $this->files = $FILES;
        $this->server = $SERVER;
        $this->cookie = $COOKIE;
        $this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $this->requested_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/' ;
    }

    public static function createFromGlobals(){
        return new Request($_GET,$_POST,$_FILES,$_SERVER,$_COOKIE);
    }

    /**
     * Get query param passed by URL
     * @param $key
     * @return value or false
     */
    public function get($key){
        return isset($this->get[$key]) ? $this->get[$key] : false;
    }

    /**
     * Get post variables
     * @param $key
     * @return value or false
     */
    public function post($key){
        return isset($this->post[$key]) ? $this->post[$key] : false;
    }

    public function server($key){
        return isset($this->server[$key]) ? $this->server[$key] : false;
    }

    public function cookie($key){
        return isset($this->cookie[$key]) ? $this->cookie[$key] : false;
    }

    /**
     * Returns the Request Body content from POST,PUT
     * For more info see: http://php.net/manual/en/wrappers.php.php
     */
    public function getBody(){
        if ($this->body == null)
            $this->body = file_get_contents('php://input');
        return $this->body;
    }

    /**
     * Get headers received
     * @param $key
     * @return value or false
     */
    public function header($key){
        return isset($_SERVER['HTTP_'.strtoupper($key)]) ? $_SERVER['HTTP_'.strtoupper($key)] : false;
    }

    public function getMethod(){
        return $this->method;
    }

    public function  getRequestedUri(){
        return $this->requested_uri;
    }
}

/**
 * Class View
 * Load a view File with access to data and Framework
 * @author Julio Cesar Martin
 */
class View
{
    protected $data;
    protected $framework;
    protected $src;
    protected $layout;

    /**
     * @param $src Source file to load
     * @param array $vars Associative key , values
     * @param null $framework isntance
     */
    public function __construct($src, array $vars = array(), App $framework = null, $layout = ''){
        $this->data = $vars;
        $this->framework = $framework;
        $this->src = $src;
        $this->layout = $layout;
    }

    /**
     * Renders a view
     * @throws Exception if View not found
     */
    public function load(){
        $app = $this->framework;
        $data = $this->data; //deprecated, vars are passed directly since version 0.0.4
        extract($this->data, EXTR_OVERWRITE);//set global all variables to the view

        if (file_exists($this->src)) {
            $block = ['title' => '', 'content' => '', 'scripts' => '', 'stylesheets' => []];
            ob_start();
            include_once($this->src); //scoped to this class
            $output = ob_get_clean();
            if(!empty($this->layout) && file_exists($this->layout)) {
                $block['content'] = $output;
                ob_start();
                include_once($this->layout);
                $output = ob_get_clean();
            }
            echo $output;
        } else {
            if($this->framework ){
                if($app->getEnvironment() == ENV_DEV)
                    return $app->error("View filename '{$this->src}' NOT found in '". VIEWS_ROUTE."'.<br/>
                     Maybe you need to change txhe App::APP_DIR or App::VIEW_DIR Constant to your current folder structure.",2);
                else
                    return $app->error('',2);
            }
        }
    }
}
