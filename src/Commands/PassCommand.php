<?php
namespace Apie\FtpServer\Commands;

use Apie\Common\Exceptions\CanNotLoginException;
use Apie\Common\LoginService;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class PassCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $loginService = $apieContext->getContext(LoginService::class, false);
        if ($loginService instanceof LoginService) {
            $username = $apieContext->getContext(FtpConstants::USERNAME, false);
            if ($username) {
                try {
                    $authenticated = $loginService->authorize($username, $arg, $apieContext);
                    $conn->write("230 User logged in\r\n");
                    return $apieContext->withContext(ContextConstants::AUTHENTICATED_USER, $authenticated);
                } catch (CanNotLoginException) {
                }
            }
            if ($username === 'anonymous' && $arg === '') {
                $conn->write("230 User logged in\r\n");
                return $apieContext;
            }
        } else {
            $conn->write("230 User logged in\r\n");
            return $apieContext;
        }
        $conn->write("430 Invalid username/password\r\n");
        return $apieContext;
    }
}
