<?PHP
namespace EventConnector;

/**
 * <h1>Событийная модель EventConnector</h1>
 *
 * Событийная модель Connector представляет собою
 * упрощённый вариант от
 * http://www.w3.org/TR/DOM-Level-3-Events/
 *
 * В отличие от W3C Connector не использует оптимизацию по типам событий,
 * и поэтому текущая реализация не рекомендована с точки зрения производительности,
 * если вам требуется обработать лишь один тип событий.
 *
 * Connector так же не использует фазы событий (по сравнению с моделью W3C).
 *
 * Событийная модель используется для того,
 * чтобы выполнить условия «знаний» между сущностями системы,
 * и сделать код максимально гибким и устойчивым к рефакторингу.
 *
 * Например, сегодня мы решили добавить в наш код «запись в журнал».
 * Мы вынуждены в каждый метод, являющийся важным сделать как минимум вызов новой функции:
 * <code>
 * public function my()
 * {
 * [old code here]
 * Log_operation(…);
 * }
 * </code>
 *
 * Событийная модель EventConnector позволяет обойтись без модификации кода,
 * путём создания стека из обработчиков событий, и списка известных событий,
 * которые определяются для каждой сущности проекта раздельно.
 *
 * <h2>Сущности EventConnector</h2>
 *
 * EventConnector основывается на сущностях:
 * - ConnectorPoint - Узел связывания
 * - Connector      - Слушатель событий
 * - Event          - Событие
 * - Owner          - Владелец события
 *
 * */

/**
 * Интерфейс для виртуального слушателя событий
 * Интерфейс наследуют объекты, которые поддерживают событийную
 * модель работы.
 */
interface ConnectorI
{
    /**
     * Позиция обработчика в начале очереди
     */
    const POS_START                 = true;

    /**
     * Позиция обработчика в конце очереди
     */
    const POS_END                   = null;

    /**
     * Добавление уникального объекта в очередь
     */
    const POS_UNIQUE                = false;

    /**
     * Метод добавляет слушателя событий $events.
     * В роли "слушателя" выступает callback тип,
     * которая может быть как функцией, так и объектом
     * или объектом с интерфейсом ConnectorI.
     *
     * callback тип должен соответствовать описанию
     * PHP::call_user_func()
     *
     * @param       callable          $listener     Слушатель события
     * @param       int|string|bool   $index        Индекс
     *
     * @return      int                             Позиция слушателя
     */
    public function listener_add(callable $listener, $index = self::POS_END);

    /**
     * Метод удаляет слушателя событий из стека.
     * Параметр $listener может быть номером слушателя
     * либо самим слушателем.
     *
     *
     * @param       object|int          $listener     Индекс слушателя или слушатель
     *
     * @return      object|null
     */
    public function listener_del($listener);

    /**
     * Метод возбуждает обработку события $event
     *
     * @param       EventI              $event      Событие
     * @param       mixed               $data       Дополнительный контекст
     */
    public function listener_raise(EventI $event, $data = null);

    /**
     * @return      mixed
     */
    public function __invoke();
}