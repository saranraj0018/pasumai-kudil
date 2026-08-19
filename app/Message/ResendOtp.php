<?php

namespace App\Message;

use App\Contracts\Message;

class ResendOtp implements Message {

    public function __construct(public string $otp) {
    }

    /**
     * Template ID for Login
     * @return string
     */
    public function template(): string {
        return '1777178705574472161';
    }

    /**
     * Message Template for OTP
     * @param mixed $dataset
     * @return string
     */
    public function message(): string {
        return "Dear User, Your OTP for PasumaiKudil login is $this->otp. This OTP has been resent at your request and is valid for 3 minutes. Do not share this OTP with anyone. https://pasumaikudilorganic.com/ - Team PasumaiKudil";
    }
}

