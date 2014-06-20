<?php
namespace Themosis\Route;

use Themosis\Core\Request;

class Route {

    /**
     * The WordPress template condition.
     *
     * @var string
     */
    protected $condition;

    /**
     * HTTP methods
     *
     * @var array
     */
    protected $methods;

    /**
     * Route actions.
     *
     * @var array
     */
    protected $action;

    /**
     * The array of matched parameters.
     * Parameters passed to the route callback or controller action method.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * WordPress conditional tags.
     *
     * @var array
     */
    protected $conditions = array(
        '404'			       => 'is_404',
        'archive'		       => 'is_archive',
        'attachment'	       => 'is_attachment',
        'author'		       => 'is_author',
        'category'		       => 'is_category',
        'date'			       => 'is_date',
        'day'			       => 'is_day',
        'front'			       => 'is_front_page',
        'home'			       => 'is_home',
        'month'			       => 'is_month',
        'page'			       => 'is_page',
        'paged'			       => 'is_paged',
        'postTypeArchive'      => 'is_post_type_archive',
        'search'		       => 'is_search',
        'subpage'		       => 'themosis_is_subpage',
        'single'		       => 'is_single',
        'sticky'		       => 'is_sticky',
        'singular'		       => 'is_singular',
        'tag'			       => 'is_tag',
        'tax'			       => 'is_tax',
        'template'             => 'themosisIsTemplate',
        'time'			       => 'is_time',
        'year'			       => 'is_year'
    );

    /**
     * Build a Route instance.
     *
     * @param array|string $methods
     * @param string $condition
     * @param mixed $action
     */
    public function __construct($methods, $condition, $action)
    {
        $this->methods = (array) $methods;
        $this->condition = $this->parseCondition($condition);
        $this->action = $this->parseAction($action);
    }

    /**
     * Parse the route action into a standard array.
     *
     * @param \Closure|array $action
     * @return array
     */
    protected function parseAction($action)
    {
        // If the action is already a Closure instance, we will just set that instance
        // as the "uses" property, because there is nothing else we need to do when
        // it is available. Otherwise we will need to find it in the action list.
        if(is_callable($action)){

            return array('uses' => $action);

        } elseif (!isset($action['uses'])){

            // If no "uses" property has been set, we will dig through the array to find a
            // Closure instance within this list. We will set the first Closure we come
            // across into the "uses" property that will get fired off by this route.
            $action['uses'] = $this->findClosure($action);
        }

        return $action;
    }

    /**
     * Return the real WordPress conditional tag.
     *
     * @param string $condition
     * @return string
     * @throws RouteException
     */
    protected function parseCondition($condition)
    {
        if(isset($this->conditions[$condition])){

            return $this->conditions[$condition];

        }

        throw new RouteException('The route condition ['.$condition.'] is no found.');
    }

    /**
     * Find the Closure in an action array.
     *
     * @param array $action
     * @return \Closure
     */
    protected function findClosure(array $action)
    {
        return array_first($action, function($key, $value){

            return is_callable($value);

        });
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     * @throws \Exception
     */
    public function parameters()
    {
        if(isset($this->parameters)){

            return array_map(function($value){

                return is_string($value) ? rawurldecode($value) : $value;

            }, $this->parameters);
        }

        throw new \Exception("Route is not bound.");
    }

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function parametersWithoutNulls()
    {
        return array_filter($this->parameters(), function($p){
            return !is_null($p);
        });
    }

    /**
     * Run the route action and return the response.
     * A string or a View.
     *
     * @return mixed
     */
    public function run()
    {
        $parameters = array_filter($this->parameters(), function($p) { return isset($p); });

        return call_user_func_array($this->action['uses'], $parameters);
    }

    /**
     * Get the HTTP verbs the route responds to.
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Get the WordPress condition.
     *
     * @return string
     */
    public function condition()
    {
        return $this->condition;
    }

    /**
     * Get the action array for the route.
     *
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

} 