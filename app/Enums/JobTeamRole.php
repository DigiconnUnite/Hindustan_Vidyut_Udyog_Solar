<?php

namespace App\Enums;

enum JobTeamRole: string
{
    case LeadTechnician = 'lead_technician';
    case Technician = 'technician';
    case SupervisingStaff = 'supervising_staff';
}
