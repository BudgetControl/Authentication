<?php
declare(strict_types=1);

namespace Budgetcontrol\Test\Libs;

use BudgetcontrolLibs\Mailer\View\ViewInterface;

class ClientMail {

    public function send(string|array $emailTo, string $subject, ViewInterface $view) 
    {
        // Send email
        @unlink(__DIR__ . '/../assertions/email.txt');
        file_put_contents(__DIR__ . '/../assertions/email.txt', $emailTo . $subject . $view->view());
    }
}