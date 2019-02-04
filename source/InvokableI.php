<?PHP
namespace EventConnector;

/**
 * Интерфейс InvokableI для объектов,
 * которые могут быть использованы как "слушатели"
 * так и подписчики.
 */
interface InvokableI
{
    /**
     * @param   EventI      $event      Event
     * @param   null        $data       Data
     *
     * @return  mixed
     */
    public function invoke_event_handler(EventI $event, $data = null);
}