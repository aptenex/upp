<?php

namespace Aptenex\Upp\Exception;

use Aptenex\Upp\Los\Debug\DebugException;
use Throwable;

class BaseException extends \Exception
{
    use ArgsTrait;
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, ?array $args = [])
    {
        $this->setArgs($args);
        parent::__construct($message, $code, $previous);
    }
    
    public static function withArgs($message = '', ?array $args = []): self
    {
        return new self($message, 0, null, $args);
    }
    
    public function toDebugExceptionArray(): array
    {
        return (new DebugException( $this->getCode(), $this->getMessage(),$this->getFile(), $this->getLine(), $this->getArgs()))->toArray();
    }
}