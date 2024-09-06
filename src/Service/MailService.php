<?php

namespace Budgetcontrol\Authentication\Service;

use Budgetcontrol\Authentication\Exception\AuthException;
use MLAB\SdkMailer\Service\EmailService;
use MLAB\SdkMailer\View\AuthMail;
use Budgetcontrol\Authentication\Facade\Mail as ClientMail;
use BudgetcontrolLibs\Mailer\View\RecoveryPasswordView;
use BudgetcontrolLibs\Mailer\View\SignUpView;
use Illuminate\Support\Facades\Log;

class MailService
{

    public static function send_signUpMail(string $to, string $name, string $token)
    {
        try {
            $view = new SignUpView();
            $view->setConfirmLink(env('APP_URL', 'http://localhost') . '/app/auth/confirm/' . $token);
            $view->setUserName($name);
            $view->setUserEmail($to);
    
            ClientMail::send($to, 'Sign Up Confirmation', $view);

        } catch (\Throwable $e) {
            Log::critical('Could not send sign up email', ['exception' => $e]);
            throw new AuthException('Could not send sign up email', 500, $e);
        }
    }

    public function send_resetPassowrdMail(string $to, string $name, string $token)
    {

        try {
            $view = new RecoveryPasswordView();
            $view->setLink(env('APP_URL', 'http://localhost') . '/app/auth/reset-password/' . $token);
            $view->setUserName($name);
            $view->setUserEmail($to);

            ClientMail::send($to, 'Reset Password', $view);

        } catch (\Throwable $e) {
            Log::critical('Could not send reset password email', ['exception' => $e]);
            throw new AuthException('Could not send reset password email', 500, $e);
        }
    }
}
