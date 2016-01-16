<?php
/**
 * Redis Counter implements fast atomic counters using Redis storage.
 * User: aschuurman
 * Date: 2016-01-16
 * Time: 16:30
 */

namespace drsdre\redis;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Component;
use yii\redis\Connection;

class Counter extends Component {

	/**
	 * @var Connection|string|array the Redis [[Connection]] object or the application component ID of the Redis [[Connection]].
	 * This can also be an array that is used to create a redis [[Connection]] instance in case you do not want do configure
	 * redis connection as an application component.
	 * After the Cache object is created, if you want to change this property, you should only assign it
	 * with a Redis [[Connection]] object.
	 */
	public $redis = 'redis';

	/**
	 * Initializes the redis Counter component.
	 * This method will initialize the [[redis]] property to make sure it refers to a valid redis connection.
	 * @throws InvalidConfigException if [[redis]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->redis)) {
			$this->redis = Yii::$app->get($this->redis);
		} elseif (is_array($this->redis)) {
			if (!isset($this->redis['class'])) {
				$this->redis['class'] = Connection::className();
			}
			$this->redis = Yii::createObject($this->redis);
		}
		if (!$this->redis instanceof Connection) {
			throw new InvalidConfigException("Counter::redis must be either a Redis connection instance or the application component ID of a Redis connection.");
		}
	}

	/**
	 * Checks whether a specified key exists in the counter store.
	 * This can be faster than getting the value from the counter store if the data is big.
	 * @param string $key a key identifying the counter.
	 * @return boolean true if a value exists in counter store, false if the value is not in the counter store or expired.
	 */
	public function exists($key)
	{
		return (bool) $this->redis->executeCommand('EXISTS', [$key]);
	}

	/**
	 * Gets a counter identified by key
	 *
	 * @param string $key a key identifying the counter.
	 * @return boolean whether the value is successfully stored
	 */
	public function get($key)
	{
		return $this->redis->executeCommand('GET', [$key]);
	}

	/**
	 * Sets a counter identified by key with amount
	 *
	 * @param string $key a key identifying the counter.
	 * @param integer $amount the value to incremented with
	 * @param integer $expire the number of seconds in which the stored value will expire. 0 means never expire.
	 * @return boolean whether the value is successfully stored
	 * @throws Exception
	 */
	public function set($key, $amount = 0, $expire = 0)
	{
		if (!is_int($amount)) {
			throw new Exception('Counter amount can only be an integer value');
		}
		if ($expire == 0) {
			return (bool) $this->redis->executeCommand('SET', [$key, $amount, 'NX']);
		} else {
			$expire = (int) ($expire * 1000);

			return (bool) $this->redis->executeCommand('SET', [$key, $amount, 'PX', $expire, 'NX']);
		}
	}

	/**
	 * Deletes a value with the specified key from the counter store
	 * @param string $key a key identifying counter.
	 * @return boolean if no error happens during deletion
	 */
	public function delete($key)
	{
		return (bool) $this->redis->executeCommand('DEL', [$key]);
	}

	/**
	 * Increment a counter identified by key with optional amount
	 *
	 * @param string $key a key identifying the counter.
	 * @param integer $amount the value to increment with
	 * @return boolean whether the counter is successfully stored
	 * @throws Exception
	 */
	public function incr($key, $amount = 1)
	{
		if (!is_int($amount)) {
			throw new Exception('Counter amount can only be an integer value');
		}
		return (bool) $this->redis->executeCommand('INCRBY', [$key, $amount]);
	}

	/**
	 * Decrement a counter identified by key with optional amount
	 *
	 * @param string $key a key identifying the counter.
	 * @param integer $amount the value to decrement with
	 * @return boolean whether the counter is successfully stored
	 * @throws Exception
	 */
	public function decr($key, $amount = 1)
	{
		if (!is_int($amount)) {
			throw new Exception('Counter amount can only be an integer value');
		}
		return (bool) $this->redis->executeCommand('DECRBY', [$key, $amount]);
	}
}