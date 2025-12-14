<?php
namespace Apie\FtpServer\Enums;

enum PortStatus: string
{
    case Available = 'available';
    case InUse = 'in_use';
    case Error = 'error';
}
