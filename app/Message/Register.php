<?php

namespace App\Message;

use App\Contracts\Message;

class Register implements Message {




    public function __construct(public string $otp) {
    }



    /**
     * Template ID for Login
     * @return string
     */
    public function template(): string {
        return '1777178696804248764';
    }




    /**
     * Message Template for OTP
     * @param mixed $dataset
     * @return string
     */
    public function message(): string {
        return "Dear User, Your OTP for PasumaiKudil registration is $this->otp. It is valid for 3 minutes. Do not share this OTP with anyone. https://pasumaikudilorganic.com/ - Team PASUMAI KUDIL";
    }
}

