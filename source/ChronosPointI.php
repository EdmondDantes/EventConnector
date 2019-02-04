<?PHP
namespace EventConnector;

/**
 * Узел Chronos.
 * Интерфейс для описания конечного узла,
 * который работает по алгоритму хроноса.
 *
 */
interface ChronosPointI
{
    /**
     * Метод возвращает объект хроноса.
     *
     * @return      ChronosI
     */
    public function сhronos();
}