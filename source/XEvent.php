<?PHP
namespace EventConnector;

/**
 * Реализация сущности "Событие".
 */
class XEvent implements EventI
{
    /**
     * @var string
     */
    protected $sysname;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var object
     */
    protected $owner;
    /**
     * @var mixed
     */
    protected $result;

    /**
     * Конструктор события
     *
     * @param       string      $sysname        Системное имя события
     * @param       array       $params         Параметры
     * @param       object      $owner          Объект, который создал событие
     */
    public function __construct($sysname, array $params = null, $owner = null)
    {
        $this->sysname          = $sysname;
        $this->params           = $params;
        $this->owner            = $owner;
    }

    public function event_sysname()
    {
        return $this->sysname;
    }

    public function event_params()
    {
        return $this->params;
    }

    public function event_owner()
    {
        return $this->owner;
    }

    public function event_result()
    {
        return $this->result;
    }

    public function event_set_result($result)
    {
        $this->result       = $result;

        return $this;
    }
}