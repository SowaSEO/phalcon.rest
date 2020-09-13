<?php

/* 
 * Users
 * 
 */

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\Messages\Message;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
//use Phalcon\Validation\Validator\InclusionIn;

class Users extends Model
{
    
    
    
    
    public function validation()
    {
       
        $validator = new Validation();              

        $validator->add(
            'login',
            new Uniqueness(
                [
                    'message' => 'The Users must be unique',
                ]
            )
        );

//        if ($this->year < 0) {
//            $this->appendMessage(
//                new Message('The year cannot be less than zero')
//            );
//        }

        if ($this->validationHasFailed() === true) {
            return false;
        }
    }
}