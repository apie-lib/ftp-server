<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Lists\StringList;

interface FtpFeatureCommand extends CommandInterface
{
    public function getFeatures(): StringList;
}
