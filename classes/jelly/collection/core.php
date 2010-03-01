<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Jelly_Collection encapsulates a Database_Result object. It has the exact same API.
 * 
 * It offers a few special features that make it useful:
 * 
 *  * Only one model is instantiated for the whole result set, which 
 *    is significantly faster in terms of performance.
 *  * It is easily extensible, so things like polymorphism and 
 *    recursive result sets can be easily implemented.
 * 
 * Jelly_Collection likes to know what model its result set is related to,
 * though it's not required. Some features may disappear, however, if 
 * it doesn't know the model it's working with.
 *
 * @package Jelly
 */
class Jelly_Collection_Core implements Iterator, Countable, SeekableIterator, ArrayAccess
{
	/**
	 * @var Jelly The current model we're placing results into
	 * 
	 */
	protected $_model = NULL;
	
	/**
	 * @var mixed The current result set
	 */
	protected $_result = NULL;
	
	/**
	 * Return all of the rows in the result as an array.
	 *
	 * @param   string  column for associative keys
	 * @param   string  column for values
	 * @return  array
	 */
	public function as_array($key = NULL, $value = NULL)
	{
		if ($this->_model)
		{
			$meta = Jelly::meta($this->_model);
			
			foreach (array('key', 'value') as $var)
			{
				if ($field = $meta->fields($$var))
				{
					$$var = $field->column;
				}
			}
		}
		
		return $this->_result->as_array($key, $value);
	}
	
	/**
	 * Tracks a database result
	 *
	 * @param  mixed   $model 
	 * @param  mixed  $result 
	 */
	public function __construct($model, $result)
	{
		if ($model)
		{
			// Convert to a model
			$model = Jelly::class_name($model);

			// Instantiate the model, which we'll continually
			// fill with values when iterating
			$this->_model = new $model;
		}
		
		$this->_result = $result;
	}
	
	/**
	 * Converts MySQL Results to Cached Results, since MySQL resources are not serializable.
	 *
	 * @return array
	 * @author Paul Banks
	 */
	public function __sleep()
	{
		if ($this->_result instanceof Database_MySQL_Result)
		{
			$this->_result = new Database_Result_Cached($this->_result->as_array(), '');
		}
	}
	
	/**
	 * Implementation of the Iterator interface
	 * @return $this
	 */
	public function rewind() 
	{
		$this->_result->rewind();
		return $this;
    }

	/**
	 * Implementation of the Iterator interface
	 * @return Jelly
	 */
    public function current($object = TRUE) 
	{	
		// Database_Result causes errors if you call current() 
		// on an object with no results, so we check first.	
		if ($this->_result->count())
		{
			$result = $this->_result->current();
		}
		else
		{
			$result = array();
		}
		
		if ($object AND $this->_model)
		{
			// Don't return models when we don't have one
			$result = ($result) 
			        ? $this->_model->load_values($result, TRUE) 
			        : $this->_model->clear();
		}
		
		return $result;
    }

	/**
	 * Implementation of the Iterator interface
	 * @return int
	 */
    public function key() 
	{
        return $this->_result->key();
    }

	/**
	 * Implementation of the Iterator interface
	 * @return $this
	 */
    public function next() 
	{
        $this->_result->next();
		return $this;
    }

	/**
	 * Implementation of the Iterator interface
	 * @return boolean
	 */
    public function valid() 
	{
		return $this->_result->valid();;
    }

	/**
	 * Implementation of the Countable interface
	 * @return boolean
	 */
    public function count() 
	{
		return $this->_result->count();;
    }

	/**
	 * Implementation of SeekableIterator
	 *
	 * @param  mixed   $offset 
	 * @return boolean
	 */
	public function seek($offset)
	{
		return $this->_result->seek($offset);
	}

	/**
	 * ArrayAccess: offsetExists
	 */
	public function offsetExists($offset)
	{
		return $this->_result->offsetExists($offset);
	}

	/**
	 * ArrayAccess: offsetGet
	 */
	public function offsetGet($offset)
	{
		return $this->_result->offsetGet($offset);
	}

	/**
	 * ArrayAccess: offsetSet
	 *
	 * @throws  Kohana_Exception
	 */
	final public function offsetSet($offset, $value)
	{
		throw new Kohana_Exception('Jelly results are read-only');
	}

	/**
	 * ArrayAccess: offsetUnset
	 *
	 * @throws  Kohana_Exception
	 */
	final public function offsetUnset($offset)
	{
		throw new Kohana_Exception('Jelly results are read-only');
	}
}
