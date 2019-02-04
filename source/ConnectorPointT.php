<?PHP
namespace EventConnector;

/**
 * Простой типаж для имплиментации ConnectorPointI
 */
trait ConnectorPointT
{
    /**
     * Коннектор для использования
     * @var ConnectorI
     */
    protected $connector;

    /**
     * Метод устанавливает обработчик событий,
     * если определён параметр $connector.
     * Если параметр $connector не определён,
     * метод возвращает текущий обработчик, если он есть.
     *
     * @param       ConnectorI      $connector      Конектор
     *
     * @return      ConnectorI|null
     */
    public function connector(ConnectorI $connector = null)
    {
        $result                     = $this->connector;

        if($connector instanceof ConnectorI)
        {
            $this->connector        = $connector;

            return $result;
        }
        elseif($this->connector === null)
        {
            $this->connector        = new XConnector();

            return $this->connector;
        }

        return $result;
    }

    protected function _raise(EventI $event)
    {
        if($this->connector !== null)
        {
            return $this->connector->listener_raise($event);
        }

        return null;
    }
}