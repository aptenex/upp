<?php

namespace Aptenex\Upp\Exception;

trait ArgsTrait
{
    /**
     * We can specify arguments for an exception. The purpose is to provide context as to why the Exception was thrown.
     * @var array
     */
    private  $args = [];
    
    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
    
    /**
     * @param array $args
     * @return self
     */
    public function setArgs(array $args): self
    {
        $this->args = $args;
        return $this;
    }
    
}