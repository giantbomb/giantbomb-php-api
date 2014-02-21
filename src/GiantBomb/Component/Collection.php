<?php

namespace GiantBomb\Component;


class Collection implements \IteratorAggregate, \Countable
{
	/**
	 * @var array
	 */
	protected $data = array();

	public function __construct( array $data = array() )
	{
		$this->data = $data;
	}

	/**
	 * Returns the data.
	 *
	 * @return array An array of data
	 *
	 * @api
	 */
	public function all()
	{
		return $this->data;
	}

	/**
	 * Returns the data keys.
	 *
	 * @return array An array of data keys
	 *
	 * @api
	 */
	public function keys()
	{
		return array_keys( $this->data );
	}

	/**
	 * Replaces the current data by a new set.
	 *
	 * @param array $data An array of data
	 *
	 * @api
	 */
	public function replace( array $data = array() )
	{
		$this->data = $data;
	}

	/**
	 * Adds data.
	 *
	 * @param array $data An array of data
	 *
	 * @api
	 */
	public function add( array $data = array() )
	{
		$this->data = array_replace( $this->data, $data );
	}

	/**
	 * Inserts Data data.
	 *
	 * @param mixed $data
	 *
	 * @api
	 */
	public function insert( $data )
	{
		$this->data[] = $data;
	}

	/**
	 * Returns a data by name.
	 *
	 * @param string  $path    The key
	 * @param mixed   $default The default value if the data key does not exist
	 * @param boolean $deep    If true, a path like foo[bar] will find deeper items
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @api
	 */
	public function get( $path, $default = null, $deep = false )
	{
		if ( !$deep || false === $pos = strpos( $path, '[' ) ) {
			return array_key_exists( $path, $this->data ) ? $this->data[ $path ] : $default;
		}

		$root = substr( $path, 0, $pos );
		if ( !array_key_exists( $root, $this->data ) ) {
			return $default;
		}

		$value      = $this->data[ $root ];
		$currentKey = null;
		for ( $i = $pos, $c = strlen( $path ); $i < $c; $i++ ) {
			$char = $path[ $i ];

			if ( '[' === $char ) {
				if ( null !== $currentKey ) {
					throw new \InvalidArgumentException( sprintf(
						'Malformed path. Unexpected "[" at position %d.',
						$i
					) );
				}

				$currentKey = '';
			} elseif ( ']' === $char ) {
				if ( null === $currentKey ) {
					throw new \InvalidArgumentException( sprintf(
						'Malformed path. Unexpected "]" at position %d.',
						$i
					) );
				}

				if ( !is_array( $value ) || !array_key_exists( $currentKey, $value ) ) {
					return $default;
				}

				$value      = $value[ $currentKey ];
				$currentKey = null;
			} else {
				if ( null === $currentKey ) {
					throw new \InvalidArgumentException( sprintf(
						'Malformed path. Unexpected "%s" at position %d.',
						$char,
						$i
					) );
				}

				$currentKey .= $char;
			}
		}

		if ( null !== $currentKey ) {
			throw new \InvalidArgumentException( sprintf( 'Malformed path. Path must end with "]".' ) );
		}

		return $value;
	}

	/**
	 * Sets a data by name.
	 *
	 * @param string $key   The key
	 * @param mixed  $value The value
	 *
	 * @api
	 */
	public function set( $key, $value )
	{
		$this->data[ $key ] = $value;
	}

	/**
	 * Returns true if the data is defined.
	 *
	 * @param string $key The key
	 *
	 * @return Boolean true if the data exists, false otherwise
	 *
	 * @api
	 */
	public function has( $key )
	{
		return array_key_exists( $key, $this->data );
	}

	/**
	 * Removes a data.
	 *
	 * @param string $key The key
	 *
	 * @api
	 */
	public function remove( $key )
	{
		unset( $this->data[ $key ] );
	}

	/**
	 * Returns the alphabetic characters of the data value.
	 *
	 * @param string  $key     The data key
	 * @param mixed   $default The default value if the data key does not exist
	 * @param boolean $deep    If true, a path like foo[bar] will find deeper items
	 *
	 * @return string The filtered value
	 *
	 * @api
	 */
	public function getAlpha( $key, $default = '', $deep = false )
	{
		return preg_replace( '/[^[:alpha:]]/', '', $this->get( $key, $default, $deep ) );
	}

	/**
	 * Returns the alphabetic characters and digits of the data value.
	 *
	 * @param string  $key     The data key
	 * @param mixed   $default The default value if the data key does not exist
	 * @param boolean $deep    If true, a path like foo[bar] will find deeper items
	 *
	 * @return string The filtered value
	 *
	 * @api
	 */
	public function getAlnum( $key, $default = '', $deep = false )
	{
		return preg_replace( '/[^[:alnum:]]/', '', $this->get( $key, $default, $deep ) );
	}

	/**
	 * Returns the digits of the data value.
	 *
	 * @param string  $key     The data key
	 * @param mixed   $default The default value if the data key does not exist
	 * @param boolean $deep    If true, a path like foo[bar] will find deeper items
	 *
	 * @return string The filtered value
	 *
	 * @api
	 */
	public function getDigits( $key, $default = '', $deep = false )
	{
		// we need to remove - and + because they're allowed in the filter
		return str_replace( array( '-', '+' ), '', $this->filter( $key, $default, $deep, FILTER_SANITIZE_NUMBER_INT ) );
	}

	/**
	 * Returns the data value converted to integer.
	 *
	 * @param string  $key     The data key
	 * @param mixed   $default The default value if the data key does not exist
	 * @param boolean $deep    If true, a path like foo[bar] will find deeper items
	 *
	 * @return integer The filtered value
	 *
	 * @api
	 */
	public function getInt( $key, $default = 0, $deep = false )
	{
		return (int)$this->get( $key, $default, $deep );
	}

	/**
	 * Filter key.
	 *
	 * @param string  $key     Key.
	 * @param mixed   $default Default = null.
	 * @param boolean $deep    Default = false.
	 * @param integer $filter  FILTER_* constant.
	 * @param mixed   $options Filter options.
	 *
	 * @see http://php.net/manual/en/function.filter-var.php
	 *
	 * @return mixed
	 */
	public function filter( $key, $default = null, $deep = false, $filter = FILTER_DEFAULT, $options = array() )
	{
		$value = $this->get( $key, $default, $deep );

		// Always turn $options into an array - this allows filter_var option shortcuts.
		if ( !is_array( $options ) && $options ) {
			$options = array( 'flags' => $options );
		}

		// Add a convenience check for arrays.
		if ( is_array( $value ) && !isset( $options[ 'flags' ] ) ) {
			$options[ 'flags' ] = FILTER_REQUIRE_ARRAY;
		}

		return filter_var( $value, $filter, $options );
	}

	/**
	 * Returns an iterator for data.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance
	 */
	public function getIterator()
	{
		return new \ArrayIterator( $this->data );
	}

	/**
	 * Returns the number of data.
	 *
	 * @return int The number of data
	 */
	public function count()
	{
		return count( $this->data );
    }

    /**
     * Shift an element off the beginning of the collection
     */
    public function shift()
    {
        return array_shift( $this->data );
    }

    /**
     * Pop an element off the end of the collection
     */
    public function pop()
    {
        return array_pop( $this->data );
    }
} 
