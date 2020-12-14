<?PHP
namespace EventConnector;

/**
 * Описание события
 */
interface EventI
{
    /**
     * Системное имя события.
     *
     * @return      string
     */
    public function event_sysname(): string;
    
    /**
     * Дополнительные параметры события.
     *
     * @return      array
     */
    public function event_params(): array;

    /**
     * Объект, который создал это событие (объект владелец)
     *
     * @return      object
     */
    public function event_owner();

    /**
     * Returns current result
     *
     * @return      mixed
     */
    public function event_result();

    /**
     * @param       mixed       $result
     *
     * @return      self
     */
    public function event_set_result($result);
}