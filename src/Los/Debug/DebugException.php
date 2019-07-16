<?php

namespace Aptenex\Upp\Los\Debug;



class DebugException implements DebugInterface
{

    public const TYPE = Diagnostics::TYPE_EXCEPTION;
    private $code;
    private $message;
    private $line;
    private $file;
    private $args;
    
    public function __construct($code, $message, $file, $line, $args)
    {
    
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->args = $args;
        
    }
    
    public function toArray(): array
    {
        return [
            'type' => self::TYPE,
            'code' => $this->code,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'args'  => $this->args
        ];
    }
}

