<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Technician = 'technician';
    case Customer = 'customer';
}
