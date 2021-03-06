<?PHP
namespace EventConnector;

use Exceptions\BaseExceptionI;

use Exceptions\UnexpectedValue;

/**
 * Test class for Chronos.
 * Generated by PHPUnit on 2012-08-31 at 17:59:20.
 */
class ChronosTest                   extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Chronos
     */
    protected $object;

    /**
     * Журнал вызовов
     * @var array
     */
    protected $log = [];

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new Chronos(['first', 'second', 'third', 'fourth']);
    }

    public function setup_listeners()
    {
        $this->object->quantum('first')->listener_add([$this, 'listener_1']);
        $this->object->quantum('second')->listener_add([$this, 'listener_2']);
        $this->object->quantum('third')->listener_add([$this, 'listener_3']);
        $this->object->quantum('fourth')->listener_add([$this, 'listener_4']);
    }

    public function listener_1(/** @noinspection PhpUnusedParameterInspection */ EventI $event, $data)
    {
        $this->assertTrue($this->object->is_executing(), '$this->object->is_executing() is not TRUE');
        $this->assertData($data, 'first');
        $this->log[] = '1';
    }

    public function listener_2(/** @noinspection PhpUnusedParameterInspection */ EventI $event, $data)
    {
        $this->assertTrue($this->object->is_executing(), '$this->object->is_executing() is not TRUE');
        $this->assertData($data, 'second');
        $this->log[] = '2';
    }

    public function listener_3(/** @noinspection PhpUnusedParameterInspection */ EventI $event, $data)
    {
        $this->assertTrue($this->object->is_executing(), '$this->object->is_executing() is not TRUE');
        $this->assertData($data, 'third');
        $this->log[] = '3';
    }

    public function listener_4(/** @noinspection PhpUnusedParameterInspection */ EventI $event, $data)
    {
        $this->assertTrue($this->object->is_executing(), '$this->object->is_executing() is not TRUE');
        $this->assertData($data, 'fourth');
        $this->log[] = '4';
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {

    }

    public function testIsQuantumPossible()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $listener   = function(EventI $event, $data)
        {
            $this->assertData($data, 'second');

            $this->assertTrue($this->object->is_quantum_possible('third'));
            $this->assertTrue($this->object->is_quantum_possible('fourth'));
            $this->assertFalse($this->object->is_quantum_possible('first'));
            $this->assertFalse($this->object->is_quantum_possible('second'));

            $e      = null;

            try
            {
                $this->object->is_quantum_possible('first', BaseExceptionI::RISE);
            }
            catch(UnexpectedValue $e)
            {
            }

            $this->assertInstanceOf(UnexpectedValue::class, $e);

            $e      = null;

            try
            {
                $this->object->is_quantum_possible('second', BaseExceptionI::RISE);
            }
            catch(UnexpectedValue $e)
            {
            }

            $this->assertInstanceOf(UnexpectedValue::class, $e);
        };

        $this->object->quantum('second')->listener_add($listener);

        $this->object->execute(new XEvent('test', ['test1', 'test2']));
    }

    public function testIsQuantumPossibleError()
    {
        $this->expectException(\Exceptions\UnexpectedValue::class);
        $this->object->is_quantum_possible('unknown', BaseExceptionI::RISE);
    }

    public function testCurrentQuantum()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $listener = function(EventI $event, $data)
        {
            $this->assertData($data, 'third');

            $this->assertEquals('third', $this->object->current_quantum());
        };

        $this->object->quantum('third')->listener_add($listener);

        $this->object->execute(new XEvent('test', ['test1', 'test2']));
    }

    public function testAddQuantum()
    {
        $this->setup_listeners();

        /** @noinspection PhpUnusedParameterInspection */
        $new = function(EventI $event, $data)
        {
            $this->assertData($data, 'new');

            $this->log[] = 'new';
        };

        /** @noinspection PhpUnusedParameterInspection */
        $before_first = function(EventI $event, $data)
        {
            $this->assertData($data, 'before_first');

            $this->log[] = 'before_first';
        };

        /** @noinspection PhpUnusedParameterInspection */
        $after_first = function(EventI $event, $data)
        {
            $this->assertData($data, 'after_first');

            $this->log[] = 'after_first';
        };

        /** @noinspection PhpUnusedParameterInspection */
        $after_new = function(EventI $event, $data)
        {
            $this->assertData($data, 'after_new');

            $this->log[] = 'after_new';
        };


        $this->object->add_quantum('new');
        $this->object->add_quantum('before_first', ConnectorI::POS_START);
        $this->object->add_quantum('after_first', 'first');
        $this->object->add_quantum('after_new', 'new');

        $this->object->quantum('new')->listener_add($new);
        $this->object->quantum('before_first')->listener_add($before_first);
        $this->object->quantum('after_first')->listener_add($after_first);
        $this->object->quantum('after_new')->listener_add($after_new);

        $this->object->execute(new XEvent('test', ['test1', 'test2']));

        $log =
        [
            'before_first', '1', 'after_first',  '2', '3', '4', 'new', 'after_new'
        ];

        $this->assertEquals($log, $this->log);
    }

    public function testExecuteNext()
    {
        $this->setup_listeners();

        $result         = $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertTrue($result, 'execute_next returns not TRUE');
        $this->assertEquals('first', $this->object->current_quantum());
        $this->assertEquals(['1'], $this->log);

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertEquals('second', $this->object->current_quantum());
        $this->assertEquals(['1', '2'], $this->log);

        // 3
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        // 4
        $result         = $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertTrue($result, 'execute_next returns not TRUE');
        $this->assertEquals('fourth', $this->object->current_quantum());
        $this->assertEquals(['1', '2', '3', '4'], $this->log);

        // over bound
        $result         = $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertFalse($result, 'execute_next returns not FALSE');
    }

    public function testExecuteNextAfterExecute()
    {
        $this->expectException(\Exceptions\UnexpectedMethodMode::class);
        
        $this->setup_listeners();

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->object->execute(new XEvent('test', ['test1', 'test2']));
    }

    public function testModifyWhileExecute()
    {
        $this->setup_listeners();

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->object->add_quantum('to_end');

        $this->object->add_quantum('after_first', $this->object->current_quantum());

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertEquals('after_first', $this->object->current_quantum());

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertEquals('fourth', $this->object->current_quantum());
        $this->assertEquals(['1', '2', '3', '4'], $this->log);

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->assertEquals('to_end', $this->object->current_quantum());
    }

    public function testModifyWhileExecuteBound()
    {
        $this->setup_listeners();

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertEquals('fourth', $this->object->current_quantum());
        $this->assertEquals(['1', '2', '3', '4'], $this->log);

        $this->object->add_quantum('to_end');

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertEquals('to_end', $this->object->current_quantum());
    }

    public function testModifyWhileExecuteWithError()
    {
        $this->expectException(\Exceptions\UnexpectedValue::class);
        
        $this->setup_listeners();

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->object->add_quantum('before_first', ConnectorI::POS_START);
    }

    public function testModifyWhileExecuteWithError2()
    {
        $this->expectException(\Exceptions\UnexpectedValue::class);
        
        $this->setup_listeners();

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->object->add_quantum('after_third', 'third');
    }

    public function testAddQuantumWithError()
    {
        $this->expectException(\Exceptions\UnexpectedValue::class);
        
        $this->object->add_quantum('error', 'unknown');
    }

    public function testReset()
    {
        $this->setup_listeners();

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));
        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->object->reset_chronos();

        $this->assertEquals(null, $this->object->current_quantum());

        $this->object->execute_next(new XEvent('test', ['test1', 'test2']));

        $this->assertEquals('first', $this->object->current_quantum());

        $this->assertEquals(['1', '2', '1'], $this->log);
    }

    public function testModifyError()
    {
        $this->expectException(\Exceptions\UnexpectedMethodMode::class);
        
        $chronos        = new Chronos(['first', 'second', 'third', 'fourth'], false);

        $this->assertEquals(false, $chronos->is_modify(), '$chronos->is_modify() is not false');

        $chronos->add_quantum('error');
    }

    public function testInterruptedExecuting()
    {
        $exception      = new \Exception('Interrupted');

        $this->object->quantum('first')->listener_add(function() use($exception)
        {
            throw $exception;
        });

        $e              = null;

        try
        {
            $this->object->execute(new XEvent('test'));
        }
        catch(\Exception $e)
        {

        }

        $this->assertTrue($e === $exception, '$e === $exception');
        $this->assertFalse($this->object->is_executing(), '$this->object->is_executing() is not FALSE');
        $this->assertEquals(null, $this->object->current_quantum(), '$this->object->current_quantum() is not NULL');
    }

    public function testOverlapExecuting()
    {
        $this->expectException(\Exceptions\UnexpectedMethodMode::class);
        
        $overlap        = function()
        {
            $this->object->execute_next(new XEvent('test'));
        };

        $this->object->quantum('first')->listener_add($overlap);

        $this->object->execute_next(new XEvent('test'));
    }

    protected function assertData($data, $quantum)
    {
        $this->assertTrue(is_array($data), 'data must be an array');
        $this->assertArrayHasKey('chronos', $data);
        $this->assertArrayHasKey('quantum', $data);

        $this->assertTrue($data['chronos'] === $this->object, 'chronos must be === $this->object');
        $this->assertEquals($quantum, $data['quantum'], "data['quantum'] ({$data['quantum']}) not equal $quantum");
    }
}