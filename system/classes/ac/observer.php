<?php

class Ac_Observer {

    protected $events;

    /**
     * Assign event
     * @param   string  $name       The event name
     * @param   mixed   $callable   A callable object
     * @param   int     $priority   The event priority; 0 = high, 10 = low
     * @return  void
     */
    public function on($name, $callable, $priority = 10) {
        $name = strtolower($name);
        if (!isset($this->events[$name])) {
            $this->events[$name] = array(array());
        }
        if (is_callable($callable)) {
            $this->events[$name][(int) $priority][] = $callable;
        }
    }

    /**
     * Invoke event
     * @param   string  $name       The event name
     * @param   mixed   $eventArg   (Optional) Argument for observer functions
     * @return  mixed event functions should return the (modified?) $eventArg
     */
    public function trigger($name, $eventArg = null) {
        $name = strtolower($name);
        if (!isset($this->events[$name])) {
            $this->events[$name] = array(array());
        }
        if (!empty($this->events[$name])) {
            // Sort by priority, low to high, if there's more than one priority
            if (count($this->events[$name]) > 1) {
                ksort($this->events[$name]);
            }
            foreach ($this->events[$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        //event functions should return the (modified?) $eventArg
                        $eventArg = call_user_func($callable, $eventArg);
                    }
                }
            }
            return $eventArg;
        }
    }

    /**
     * Clear event listeners
     *
     * Clear all listeners for all events. If `$name` is
     * a valid event name, only the listeners attached
     * to that event will be cleared.
     *
     * @param   string  $name   A event name (Optional)
     * @return  void
     */
    public function off($name = null) {
        $name = strtolower($name);
        if (!empty($name) && isset($this->events[(string) $name])) {
            $this->events[(string) $name] = array(array());
        } else {
            foreach ($this->events as $key => $value) {
                $this->events[$key] = array(array());
            }
        }
    }

    /**
     * Get event listeners
     *
     * Return an array of registered events. If `$name` is a valid
     * event name, only the listeners attached to that event are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are event names and whose values are arrays of listeners.
     *
     * @param   string      $name A event name (Optional)
     * @return  array|null
     */
    public function events($name = null) {
        if (!empty($name)) {
            $name = strtolower($name);
            return isset($this->events[(string) $name]) ? $this->events[(string) $name] : null;
        } else {
            return $this->events;
        }
    }

}