<?PHP
namespace EventConnector;

/**
 * Узел связывания.
 * Интерфейс для описания конечного узла,
 * который испускает события.
 *
 * Интерфейс ConnectorPointI служит лишь для того, чтобы получить доступ к
 * ConnectorI
 *
 * Интерфейс ConnectorPointI как правило реализовывают те объекты,
 * которые владеют ConnectorI.
 */
interface ConnectorPointI
{
    /**
     * Метод устанавливает обработчик событий,
     * если определён параметр $connector.
     * Если параметр $connector не определён,
     * метод возвращает текущий обработчик, если он есть.
     *
     * Если параметр $connector === null, а внутренний коннектор
     * не был определён, он инициализируется. В этом случае
     * метод всегда вернёт ConnectorI.
     *
     * @param       ConnectorI      $connector      Конектор
     *
     * @return      ConnectorI|null
     */
    public function connector(ConnectorI $connector = null);
}