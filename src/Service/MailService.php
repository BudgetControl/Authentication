<?php
namespace Budgetcontrol\Authentication\Service;

use Budgetcontrol\SdkMailer\Domain\Transport\ArubaSmtp;
use MLAB\SdkMailer\Service\EmailService;
use Symfony\Component\Mailer\Transport\Dsn;
use MLAB\SdkMailer\View\AuthMail;

class MailService {

    private EmailService $emailService;

    public function __construct()
    {
        $dsn = new Dsn(ENV('MAIL_DRIVER','mailhog'), env('MAIL_HOST'), env('MAIL_USER'), env('MAIL_PASSWORD'));
        $this->emailService = new EmailService($dsn, env('MAIL_FROM_ADDRESS'));
    }

    public function send_signUpMail(string $to, string $name, string $token)
    {   
        $view = new AuthMail([
            'name' => $name,
            'confirm_link' =>  env('APP_URL', 'http://localhost') . '/app/auth/confirm/' . $token,
        ]);
        $view->sign_upView();

        $this->emailService->sendEmail(
            $to,
            'Sign Up Confirmation',
            $view
        );
    }

    public function send_resetPassowrdMail(string $to, string $name, string $token)
    {
        $view = new AuthMail([
            'link' =>  env('APP_URL', 'http://localhost') . '/app/auth/reset-password/' . $token,
        ]);
        $view->recovery_passwordView();

        $this->emailService->sendEmail(
            $to,
            'Reset Password',
            $view
        );
    }
}