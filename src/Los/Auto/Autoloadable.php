<?php
namespace Aptenex\Upp\Los\Auto {

    /* a normal, autoloaded class */
    class Autoloadable {
    
        public function __construct($greeting) {
            list($this->hello, $this->world) =
                explode(' ', $greeting);
        }
        
        public function __toString() {
            return sprintf(
                '%s %s',
                $this->hello, $this->world);
        }
        
        protected $hello;
        protected $world;
    }
}