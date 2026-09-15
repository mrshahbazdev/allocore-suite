<?php

use App\Models\User;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\PlanHive\Models\Project;

return [

    /*
    | Models der Host-Applikation. In Allocore ggf. anpassen.
    */
    'user_model' => User::class,
    'project_model' => Project::class,

    /*
    | Spalte am User-Model, die den Mandanten haelt. Auf null setzen,
    | wenn die App keine Mandanten kennt.
    */
    'tenant_column' => 'current_team_id',

    /*
    | Uploads
    */
    'disk' => env('ISSUEBOARD_DISK', 'public'),
    'directory' => 'issues',
    'max_upload_kb' => 8192,
    'accepted_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'csv', 'xlsx', 'docx'],

    /*
    | Wer darf welchen Status setzen? Rollenname => erlaubte Zielstatus.
    | '*' = alle. Die Rolle wird ueber config('issueboard.role_resolver') ermittelt.
    */
    'transitions' => [
        'client' => [IssueStatus::New->value, IssueStatus::WithAli->value],
        'ali' => [IssueStatus::WithAli->value, IssueStatus::WithDev->value, IssueStatus::Done->value],
        'dev' => ['*'],
        'admin' => ['*'],
    ],

    /*
    | Callback, das aus einem User eine Rolle aus der obigen Liste macht.
    | Standard: Spalte 'role' am User-Model, Fallback 'client'.
    */
    'role_resolver' => null,

    /*
    | Woechentliche Uebersichts-Mail
    */
    'digest' => [
        'enabled' => true,
        'recipients' => [], // leer = alle User mit Rolle ali/dev
        'day' => 'monday',
        'time' => '08:00',
    ],

    'route' => [
        'prefix' => 'aufgabenboard',
        'middleware' => ['web', 'auth'],
    ],
];
