<?php 
namespace Apie\FtpServer\SiteCommands;

use Apie\Core\Context\ApieContext;
use React\Socket\ConnectionInterface;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;

class StoreTestCoverageCommand implements SiteCommandInterface
{
    public function getName(): string
    {
        return 'TEST';
    }

    public function getHelpText(): string
    {
        return 'Stores the test coverage data sent by the client (tests only).';
    }

    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        if (!$arg) {
            $conn->write("501 No file path provided for coverage data\r\n");
            return $apieContext;
        }
        $coverage = $apieContext->getContext(CodeCoverage::class, false);
        if($coverage instanceof CodeCoverage) {
            $coverage->stop();
            (new PhpReport)->process($coverage, $arg);
            $conn->write("200 Test coverage data stored successfully\r\n");
        } else {
            $conn->write("550 No code coverage context available\r\n");
        }
        return $apieContext;
    }
}