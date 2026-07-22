<?php

namespace Modules\DentalTrack\Enums;

enum ReworkCause: string
{
    case MaterialDefect = 'material_defect';
    case TechniqueError = 'technique_error';
    case EquipmentIssue = 'equipment_issue';
    case DesignError = 'design_error';
    case Other = 'other';
}
