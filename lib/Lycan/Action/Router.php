<?php 

namespace Lycan\Action;

use Lycan\Support\Inflect;

class Router
{
    protected $namespace;

    protected $url;

    protected $controller;

    protected $action;
    
    protected $method='get';

    protected $name;

    protected $params;

    protected $format;

    private $_default_values;

    public function __construct($name=null, $url=null, $controller=null, $action=null,  $namespace=null, $method='get', $format=null)
    {
        $this->url          = $url;
        $this->controller   = $controller;
        $this->action       = $action;
        $this->method       = $method;
        $this->namespace    = $namespace;
        $this->name         = $name;
        $this->format       = $format;
    }

    public function getController()
    {
        return Inflect::camelize($this->controller);
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getParams()
    {
        $params = $this->params;
        unset($params[':controller']);
        unset($params[':action']);
        return $params ?: array();
    }

    public function name_space($name, $block)
    {
        $router = new Router(null, null, null, null, $name);
        $block($router);
    }

    public function resources($name, $options=array())
    {
        $only = isset($options['only']) ? $options['only'] : array();
        $only = empty($only) ? \Lycan\Action\Routes::$verbs : $only;
        foreach ($only as $verb)
        {
            $router = self::_verb_to_router($verb,$name,$this->namespace);
            Routes::$routers[$router->getName()] = $router;
        }

    }

    public function connect($url, $options=array(), $name=null)
    {
        if ( trim($url) == "" && empty($options) )
            throw new \InvalidArgumentException("url and or options must be set");

        $this->url = ( $this->namespace ? $this->namespace . "/" : null ) . $url;

        $this->controller = isset($options['controller']) ? $options['controller'] : null;
        $this->action     = isset($options['action']) ? $options['action'] : null;
        $this->method     = isset($options['method']) ? $options['method'] : null;
        $this->format     = isset($options['format']) ? $options['format'] : null;
        $this->name       = isset($options['name'])   ? $options['name']   : null;

        if (  empty($options) || $this->controller == null) {
            $this->_default_values = true;
        }
        null === $name ? Routes::$routers[] = clone $this : Routes::$routers[$name] = clone $this;
        
        $this->_default_values = null;
    }

    private function _url_combine($path)
    {
        if ( !empty($this->params) ) return $this->params;

        $format = false;
        
        $match_url = explode('/', $path);
        if ( $pos = strrpos($path, '.') ) {
            $ext = substr($path, $pos);
            $match_url[] = substr($ext, 1);
            $format = true;
        }
        $pos = strrpos($this->url, '.');
        if ( false == $format && $pos ) return array();
        
        $route_url = explode('/', $this->url);
        if ( $format && $pos ) {
            $ext = substr($this->url, $pos);
            $route_url[] = substr($ext,1);
        } elseif($format == true) {
            array_pop($match_url);
            $format = false;
        }

        if (count($route_url) >= 1 && count($match_url) >= 1 
            && count($route_url) == count($match_url) ) 
        {
            $return = array();
            $combine = array_combine($route_url, $match_url);
            foreach ($combine as $key => $value) {
                if ($key == $value)
                    continue;
                if ( $format ) {
                    $e = explode('.',$key);
                    $v = explode('.',$value);
                    $return[$e[0]] = $v[0];
                    $this->format = $v[0];
                } else {
                    $return[$key] = $value;
                }
            }
            $this->params = $return;
            return $return;
        }
        return array();
    }

    public function match($path)
    {
        $regx_url = preg_replace("/:[a-z]+/","([^/]+)", $this->url);
        $regx_url =  str_replace('/','\/', $regx_url);
        $regx_url =  str_replace('.','\.', $regx_url);
        if (preg_match("/^" . $regx_url . "$/", $path)) {
            $a = $this->_url_combine($path);
            if ( $this->_default_values ) {
                if ( empty($a) ) return false;
                $this->controller = $a[':controller'];
                $this->action = $this->action == null ? $a[':action'] : $this->action;
                return true;
            }
            return true;
        }
        return false;
    }

    private static function _verb_to_router($verb, $name, $namespace=null)
    {
        switch ($verb) {
            case 'index':
                $controller = $name;
                $action     = 'index';
                $url        = ($namespace ? "{$namspace}/" : null) . $name;
                $name       = ($namespace ? "{$namspace}_" : null) . $name;
                return new Router($name, $url, $controller, $action, $namespace);
                break;
            case 'show':
                $controller = $name;
                $action     = 'show';
                $url        = ($namespace ? "{$namspace}/" : null) . $name . "/:id";
                $name       = ($namespace ? "{$namspace}_" : null) . "show_" . Inflect::singularize($name);
                return new Router($name, $url, $controller, $action, $namespace);
                break;
            case 'add':
                $controller = $name;
                $action     = 'add';
                $url        = ($namespace ? "{$namspace}/" : null) . $name . '/new';
                $name       = ($namespace ? "{$namspace}_" : null) . "add_" . Inflect::singularize($name);
                return new Router($name, $url, $controller, $action, $namespace);
                break;
            case 'create': 
                $controller = $name;
                $action     = 'create';
                $url        = ($namespace ? "{$namspace}/" : null) . $name ;
                $name       = ($namespace ? "{$namspace}_" : null) . $name;
                return new Router($name, $url, $controller, $action, $namespace, 'post');
                break;
            case 'edit': 
                $controller = $name;
                $action     = 'edit';
                $url        = ($namespace ? "$namspace/" : null) . $name . '/:id/edit';
                $name       = ($namespace ? "$namspace_" : null) . "edit_" . $name;
                return new Router($name, $url, $controller, $action, $namespace);
                break;
            case 'update': 
                $controller = $name;
                $action     = 'update';
                $url        = ($namespace ? "$namspace/" : null) . $name . '/:id';
                $name       = ($namespace ? "$namspace_" : null) . "update_" . $name;
                return new Router($name, $url, $controller, $action, $namespace, 'put');
                break;   
            case 'destroy': 
                $controller = $name;
                $action     = 'destroy';
                $url        = ($namespace ? "$namspace/" : null) . $name . '/:id';
                $name       = ($namespace ? "$namspace_" : null) . 'destroy_' . $name;
                return new Router($name, $url, $controller, $action, $namespace, 'delete');
                break;
        }
    }
}