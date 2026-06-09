<?php

namespace App\Enums;

enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_CORPORATIVO = 'admin_corporativo';
    case ADMIN_EMPRESA = 'admin_empresa';
    case ADMIN_SUCURSAL = 'admin_sucursal';
}
