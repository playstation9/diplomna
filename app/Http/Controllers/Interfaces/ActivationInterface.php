<?php namespace App\Http\Controllers\Interfaces;

/**
 * Description of ActivationInterface
 *
 * @author Boko
 */
use App\User;

interface ActivationInterface {
    
 
    public function sendActivationCode(User $user, $to, $code);
    
    public function check($data);
    
}
