<?php

namespace App\Contracts;


interface Message {


    /**
     * Get the message Template id
     * @return string
     */
    public function template(): string;


    /**
     * Get the message subject
     * @return string
     */
    public function message(): string;
}
