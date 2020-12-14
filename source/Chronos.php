<?PHP
namespace EventConnector;

use Exceptions\BaseExceptionI;

use Exceptions\UnexpectedValue;
use Exceptions\UnexpectedMethodMode;

/**
 * # Реализация модели Квантов.
 *
 * **Chronos** - обработка событий, которые имеют предопределённый порядок появления.
 *
 * Класс реализует модель обработки событий по схеме квантов.
 * Для этого создаётся массив ConnectorI,
 * каждый элемент массива имеет имя - имя кванта.
 *
 * Кванты исполняются в том же порядке, в котором они были
 * определены изначально.
 *
 * Квант запрещается изменять или читать, если он выполняется,
 * или уже был выполнен.
 *
 */
class Chronos           implements ChronosI
{
    /**
     * Кванты времени
     *
     * @var ConnectorI[]
     */
    protected array $quantums = [];

    /**
     * Текущий квант времени
     * @var string|null
     */
    protected ?string $current = null;

    /**
     * Флаг, который разрешает изменять кванты
     * после создания объекта.
     *
     * @var boolean
     */
    protected bool $is_modify;

    /**
     * Flag equal true if Chronos executing now
     * @var bool
     */
    protected bool $is_executing = false;

    /**
     * Конструктор квантов. Метод принимает список имён квантов.
     *
     * @param       array       $quantums           Список квантов
     * @param       boolean     $is_modify          Флаг модификации
     */
    public function __construct(array $quantums = [], bool $is_modify = true)
    {
        $this->is_modify          = $is_modify;

        foreach($quantums as $name)
        {
            $this->quantums[$name] = new XConnector();
        }
    }

    public function is_quantum_possible(string $name, bool $is_rise = BaseExceptionI::MUTE): bool
    {
        try
        {
            if(!array_key_exists($name, $this->quantums))
            {
                throw new UnexpectedValue('$name', $name, 'quantum is not defined');
            }

            // Препятствует получению кванта, если он уже выполнен
            if(!is_null($this->current)
            && array_search($name, array_keys($this->quantums))
            <= array_search($this->current, array_keys($this->quantums)))
            {
                throw new UnexpectedValue('$name', $name, 'quantum already executed');
            }
        }
        catch(\Exception $e)
        {
            if($is_rise === BaseExceptionI::RISE)
            {
                throw $e;
            }

            return false;
        }

        return true;
    }

    public function is_executing(): bool
    {
        return $this->is_executing;
    }

    public function quantum(string $name): ConnectorI
    {
        $this->is_quantum_possible($name, BaseExceptionI::RISE);

        return $this->quantums[$name];
    }

    public function current_quantum(): ?string
    {
        return $this->current;
    }

    public function add_quantum($name, $after = ConnectorI::POS_END): ConnectorI
    {
        if(!$this->is_modify)
        {
            throw new UnexpectedMethodMode(__METHOD__, 'Chronos is not possible modify', 'false');
        }

        if($after === ConnectorI::POS_END)
        {
            $this->quantums[$name] = new XConnector();

            return $this->quantums[$name];
        }

        if($after === ConnectorI::POS_START)
        {
            $index          = 0;
        }
        else
        {
            if(!array_key_exists($after, $this->quantums))
            {
                throw new UnexpectedValue
                (
                    '$after',
                    $after,
                    'The quantum with name $after is not found'
                );
            }

            $index          = array_search($after, array_keys($this->quantums)) + 1;
        }

        // Квант не может быть текущим или уже выполненным
        if(!is_null($this->current)
        && $index <= array_search($this->current, array_keys($this->quantums)))
        {
            throw new UnexpectedValue
            (
                '$after',
                $after,
                'Not possible to add the quantum after the quantum already executed'
            );
        }

        $connector          = new XConnector();

        // Вставить в указанную позицию конектор
        $this->quantums     = array_merge
        (
            array_slice($this->quantums, 0, $index),
            [$name => $connector],
            array_slice($this->quantums, $index)
        );

        return $connector;
    }

    public function execute(EventI $event): void
    {
        if(!is_null($this->current))
        {
            throw new UnexpectedMethodMode
            (
                __METHOD__,
                'Chronos already started',
                $this->current
            );
        }

        $this->is_executing = true;

        try
        {
            foreach($this->quantums as $quantum => $connector)
            {
                $this->current  = $quantum;

                $connector->listener_raise($event, ['chronos' => $this, 'quantum' => $quantum]);
            }
        }
        finally
        {
            $this->current      = null;
            $this->is_executing = false;
        }
    }

    public function execute_next(EventI $event): bool
    {
        if($this->is_executing)
        {
            throw new UnexpectedMethodMode
            (
                __METHOD__,
                'Chronos already executed',
                $this->is_executing
            );
        }

        $this->current      = $this->get_next_quantum();

        if(is_null($this->current))
        {
            return false;
        }

        $this->is_executing = true;

        try
        {
            $this->quantums[$this->current]->listener_raise($event, ['chronos' => $this, 'quantum' => $this->current]);
        }
        finally
        {
            $this->is_executing = false;
        }

        return true;
    }

    public function reset_chronos(): static
    {
        $this->current      = null;

        return $this;
    }

    public function is_modify(): bool
    {
        return $this->is_modify;
    }

    /**
     * Метод возвращает следующий квант времени.
     * Если кванты завершены, метод вернёт null.
     *
     * @return string|null
     */
    protected function get_next_quantum(): ?string
    {
        if(is_null($this->current))
        {
            $res            = array_keys($this->quantums);
            return reset($res);
        }

        $quantums           = array_keys($this->quantums);

        $index              = array_search($this->current, $quantums) + 1;

        // Если больше в массиве нет квантов - вернуть null
        if($index >= count($quantums))
        {
            return null;
        }

        return $quantums[$index];
    }
}