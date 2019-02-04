<?PHP
namespace EventConnector;

use Exceptions\UnexpectedValueType;
use Exceptions\UnexpectedValue;
use Exceptions\RecursionLimitExceeded;
use EventConnector\Exceptions\EventConnectorTerminate;

/**
 * Реализация сущности "Конектор"
 */
class XConnector                implements ConnectorI
{
    /**
     * count of nesting calls
     * @var int
     */
    static protected $nesting_call_count = 0;

    /**
     * @var callable[]
     */
    protected $stack;

    /**
     * Максимальная вложенность вызова самих себя.
     * @var int
     */
    protected $max_nesting_call = 16;

    public function __construct()
    {
        $this->stack            = [];
    }

    static public function nesting_call_counter()
    {
        return self::$nesting_call_count;
    }

    public function nesting_call_count()
    {
        return self::nesting_call_counter();
    }

    /**
     * Метод добавляет слушателя событий $events.
     * В роли "слушателя" выступает callable параметр.
     *
     * @param       callable          $listener     Слушатель события
     * @param       int|string|bool   $index        Индекс
     *
     * @return      int                             Позиция слушателя
     *
     * @throws      UnexpectedValue                 If trying added yourself
     * @throws      UnexpectedValueType
     */
    public function listener_add(callable $listener, $index = self::POS_END)
    {
        // Нельзя подписать самого себя на свои же события
        if($listener === $this)
        {
            throw new UnexpectedValue('$listener', $listener, 'Can\'t added yourself');
        }

        if(self::POS_START === $index)
        {
            // Добавить в начало стека
            array_unshift($this->stack, $listener);

            return 0;
        }
        elseif(self::POS_END === $index)
        {
            // Добавить в конец стека
            array_push($this->stack, $listener);

            return count($this->stack) - 1;
        }
        elseif(self::POS_UNIQUE === $index)
        {
            if(!is_object($listener))
            {
                throw new UnexpectedValueType('$listener', $listener, 'object');
            }

            $index                  = array_search($listener, $this->stack, true);

            if($index !== false)
            {
                return $index;
            }

            $this->stack[]          = $listener;

            return count($this->stack) - 1;
        }
        elseif(!isset($this->stack[$index]))
        {
            $this->stack[$index]    = $listener;

            return $index;
        }
        else
        {
            throw new UnexpectedValue('$index', $index, 'index already exists or undefined');
        }
    }

    /**
     * Метод удаляет слушателя событий из стека.
     * Параметр $listener может быть номером слушателя
     * либо самим слушателем.
     *
     * @param       object|int          $listener     Индекс слушателя или слушатель
     *
     * @return      object|null
     *
     * @throws      UnexpectedValue
     */
    public function listener_del($listener)
    {
        if(self::POS_START === $listener)
        {
            return array_shift($this->stack);
        }
        elseif(self::POS_END === $listener)
        {
            return array_pop($this->stack);
        }
        elseif(is_object($listener))
        {
            foreach($this->stack as $i => $v)
            {
                if($v === $listener)
                {
                    unset($this->stack[$i]);
                    return $listener;
                }
            }

            return null;
        }

        throw new UnexpectedValue('$listener', $listener, '$listener is not found');
    }

    /**
     * Метод возбуждает событие.
     *
     * @param       EventI      $event      Событие
     * @param       mixed       $data       Данные события
     *
     * @return      mixed                   Результат обработки
     *
     * @throws      RecursionLimitExceeded
     */
    public function listener_raise(EventI $event, $data = null)
    {
        ++self::$nesting_call_count;

        if(self::$nesting_call_count > $this->max_nesting_call)
        {
            throw new RecursionLimitExceeded($this->max_nesting_call);
        }

        try
        {
            foreach($this->stack as $callable)
            {
                call_user_func($callable, $event, $data);
            }
        }
        catch(EventConnectorTerminate $e)
        {
            --self::$nesting_call_count;

            return $e->get_data();
        }

        --self::$nesting_call_count;

        return null;
    }

    /**
     * @return  mixed|void
     */
    public function __invoke()
    {
        return call_user_func_array([$this, 'listener_raise'], func_get_args());
    }
}