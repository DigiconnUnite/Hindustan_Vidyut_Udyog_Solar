<?php

namespace App\Enums;

enum DocumentType: string
{
    case SitePhoto = 'site_photo';
    case Agreement = 'agreement';
    case SubsidyPaper = 'subsidy_paper';
    case CompletionPhoto = 'completion_photo';
    case Other = 'other';
}
