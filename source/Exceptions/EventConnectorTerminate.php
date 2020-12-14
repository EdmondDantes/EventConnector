<?PHP
namespace EventConnector\Exceptions;

use Exceptions\BaseException;

/**
 * Исключение, которое используется для прерывания
 * цикла обработчиков.
 * Это исключение не попадает в журнал.
 */
class EventConnectorTerminate extends BaseException
{
}