<?PHP
namespace EventConnector\Exceptions;

use Exceptions\BaseException;

/**
 * Програмное исключение, которое используется для прерывания
 * цикла обработчиков.
 * Это исключение не попадает в журнал.
 */
class EventConnectorTerminate extends BaseException
{
}