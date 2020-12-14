<?PHP
namespace EventConnector;

use Exceptions\BaseExceptionI;

use Exceptions\UnexpectedValue;
use Exceptions\UnexpectedMethodMode;

interface ChronosI
{
    /**
     * Метод вернёт TRUE, если квант возможен к использованию.
     *
     * @param       string          $name    Имя кванта
     * @param       boolean         $is_rise Флаг выброса исключений
     *
     * @return      boolean
     *
     * @throws UnexpectedValue
     * @throws \Exception
     */
    public function is_quantum_possible(string $name, bool $is_rise = BaseExceptionI::MUTE): bool;
    
    /**
     * @return      bool
     */
    public function is_executing(): bool;
    
    /**
     * Метод возвращает квант, если он ещё не был выполнен.
     *
     * **Внимание**
     * Этот метод всегда проверяет, можно ли вернуть квант.
     * Если квант уже использован, или используется сейчас
     * метод выбросит исключение.
     *
     * @param       string          $name           Имя кванта
     *
     * @return      ConnectorI                      Коннектор с обработчиками
     *
     * @throws UnexpectedValue Если квант не найден или уже выполнен
     */
    public function quantum(string $name): ConnectorI;
    
    /**
     * Метод вернёт имя текущего квант, который выполняется.
     *
     * @return      string|null
     */
    public function current_quantum(): ?string;

    /**
     * Метод добавляет квант, и возвращает его.
     *
     *
     * @param       string          $name       Имя нового кванта
     * @param       string|bool     $after      После какого кванта вставить (false - в конец, true - в начало)
     *
     * @return      ConnectorI      Коннектор
     *
     * @throws      UnexpectedMethodMode
     * @throws      UnexpectedValue
     */
    public function add_quantum(string $name, string|bool $after = ConnectorI::POS_END): ConnectorI;

    /**
     * Выполнить все кванты
     *
     * @param       \EventConnector\EventI      $event  Событие-контекст
     *
     * @throws      UnexpectedMethodMode                Попытка вызвать метод после execute_next()
     */
    public function execute(EventI $event): void;

    /**
     * Метод позволяет выполнить только следующий активный квант.
     * В отличие от метода execute(), этот метод позволяет выполнять кванты по одному.
     * Метод будет возвращать true, пока выполнение продолжается.
     * Это позволяет использовать его в цикле while():
     * <code>
     *      while($chronos->execute_next($event))
     *      {
     *          ...
     *      }
     * </code>
     *
     * Метод используется тогда, когда необходимо выполнять какие-то действия
     * между квантами.
     *
     * **Внимание**
     * Нельзя вызывать метод execute, если текущий квант не равен null,
     * то есть если процесс обработки не находиться в начале.
     *
     * @param       \EventConnector\EventI      $event
     *
     * @return      boolean                     Метод вернёт false, если кванты закончились.
     */
    public function execute_next(EventI $event): bool;

    /**
     * Принудительный сброс квантов
     *
     * @return      ChronosI
     */
    public function reset_chronos(): static;

    /**
     * Метод вернёт true, если Chronos можно изменять
     *
     * @return      boolean
     */
    public function is_modify(): bool;
}