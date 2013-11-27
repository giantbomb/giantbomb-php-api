<?php
/**
 * @author    Aaron Scherer
 * @date      11/26/13
 * @copyright Underground Elephant
 */

namespace GiantBomb\Cache;

interface CacheInterface
{
	/**
	 * Fetches an entry from the cache.
	 *
	 * @param string $id The id of the cache entry to fetch.
	 *
	 * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
	 */
	function fetch($id);

	/**
	 * Tests if an entry exists in the cache.
	 *
	 * @param string $id The cache id of the entry to check for.
	 *
	 * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
	 */
	function contains($id);

	/**
	 * Puts data into the cache.
	 *
	 * @param string $id       The cache id.
	 * @param mixed  $data     The cache entry/data.
	 * @param int    $lifeTime The cache lifetime.
	 *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
	 *
	 * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
	 */
	function save($id, $data, $lifeTime = 0);

	/**
	 * Deletes a cache entry.
	 *
	 * @param string $id The cache id.
	 *
	 * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
	 */
	function delete($id);
} 