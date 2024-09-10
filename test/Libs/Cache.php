<?php
declare(strict_types=1);

namespace Budgetcontrol\Test\Libs;

use Mockery;

class Cache extends Mockery {

    private bool $gotException = false;
    private bool $shouldReturnError = false;

    public function get(string $key) {

        if($this->shouldReturnError) {
            return null;
        }

        $stdClass = new \stdClass();
        $stdClass->key = $key;
        $stdClass->value = 'value';
        $stdClass->email = 'foo@bar.com';
        $stdClass->password = 'password';
        $stdClass->id = 1;

        return $stdClass;
    }

    public function put(){
        
    }

    public function has(string $key) {
        if($this->gotException) {
            throw new \Exception('Error getting cache');
        }
        
       if($this->shouldReturnError) {
           return false;
       }

       return true;
    }
    
    public function forget() {
        
    }


    /**
     * Set the value of gotException
     *
     * @param bool $gotException
     *
     * @return self
     */
    public function setGotException(bool $gotException): self
    {
        $this->gotException = $gotException;

        return $this;
    }


    /**
     * Set the value of shouldReturnError
     *
     * @param bool $shouldReturnError
     *
     * @return self
     */
    public function setShouldReturnError(bool $shouldReturnError): self
    {
        $this->shouldReturnError = $shouldReturnError;

        return $this;
    }
}