<?php
declare(strict_types=1);

namespace Budgetcontrol\Test\Libs;

class Cache {

    private bool $gotException = false;
    private bool $shouldReturnError = false;

    public function get(string $key) {
        $stdClass = new \stdClass();
        $stdClass->key = $key;
        $stdClass->value = 'value';
        $stdClass->email = 'foo@bar.com';
        
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