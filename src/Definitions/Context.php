<?php
declare(strict_types=1);

namespace Budgetcontrol\Authentication\Definitions;

enum Context: string {
    case IOS = 'ios';
    case ANDROID = 'android';
    case WEB = 'web';
}