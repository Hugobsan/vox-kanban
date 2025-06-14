<?php

namespace App\Enums;

enum RoleInBoard: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Viewer = 'viewer';
}