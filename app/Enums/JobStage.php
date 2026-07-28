<?php

namespace App\Enums;

enum JobStage: string
{
    case Lead = 'lead';
    case SiteSurvey = 'site_survey';
    case Quotation = 'quotation';
    case Agreement = 'agreement';
    case Installation = 'installation';
    case Inspection = 'inspection';
    case Handover = 'handover';

    /**
     * Ordered pipeline, per docs/02-database-entity-document.md §4.
     *
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return [
            self::Lead,
            self::SiteSurvey,
            self::Quotation,
            self::Agreement,
            self::Installation,
            self::Inspection,
            self::Handover,
        ];
    }

    public function next(): ?self
    {
        $ordered = self::ordered();
        $index = array_search($this, $ordered, strict: true);

        return $ordered[$index + 1] ?? null;
    }
}
