<?php

/**
 *
 * AdiPHP : Rapid Development Tools (http://adilab.net)
 * Copyright (c) Adrian Zurkiewicz
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @version     0.1
 * @copyright   Adrian Zurkiewicz
 * @link        http://adilab.net
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Adi\System;

use Adi\System\Exception\CriticalSectionException;

/**
 * Allows create a critical section of code where only determine number of process can enter.
 * 
 * @author adrian
 */
class CriticalSection {

	private $id;
	private $file = array();
	private $max_process;

	/**
	 * Constructor
	 * 
	 * <code>
	 * $cs = new CriticalSection();
	 * if (!$cs->hasAccess())
	 *   die("There are other process in executing...\n");
	 * 
	 * echo "Processing...\n";
	 * $cs = NULL; // Destructs (closes) the critical section.
	 * </code>
	 * 
	 * <code>
	 * $cs = new CriticalSection();
	 * $cs->waitAccess();
	 * echo "Processing...\n";
	 * </code>
	 * 
	 * @param string $param Critical section ID, default: Auto-create from backtrace.
	 * @param integer $max_process Maximum number of process that can have simultaneous access, default: 1
	 *
	 */
	function __construct($id = NULL, $max_process = 1) {

		if (!$id) {
			$bt = debug_backtrace();
			$id = $bt[0]['file'] . $bt[0]['line'];
		}
		
		$this->id = sha1($id);

		$this->max_process = $max_process;
	}

	/**
	 * Destructor
	 */
	function __destruct() {

		foreach ($this->file as $file) {

			if ($file) {

				flock($file, LOCK_UN);

				fclose($file);
			}
		}
	}

	/**
	 * Checks if the process has access to continue
	 *
	 * @return boolean
	 */
	public function hasAccess() {

		for ($i = 0; $i < $this->max_process; $i++) {

			if ($this->check($i)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Wait to have access. 
	 * 
	 * If exceeds the maximum waiting time throw an exception of CriticalSectionException.
	 *
	 * @param integer $seconds Maximum waiting time in seconds, default: -1 (infinity)
	 * @throws CriticalSectionException
	 */
	public function waitAccess($seconds = -1) {

		$i = 0;

		while (!$this->hasAccess()) {

			if ($seconds > -1) {
				if (($i / 10) > $seconds) {

					throw new CriticalSectionException("Exceeded the maximum waiting time.");
				}
			}

			usleep(100000);

			$i++;
		}
	}

	/**
	 * Check access.
	 *
	 * @param integer $num Slot number
	 * @return boolean
	 * @throws CriticalSectionException
	 */
	private function check($num) {

		if ($num >= $this->max_process) {
			throw new CriticalSectionException("Slot number out of range.");
		}

		$file_name = "/tmp/{$this->id}-{$num}";

		if (!file_exists($file_name)) {
			file_put_contents($file_name, NULL);
		}

		if (!$this->file[$num] = fopen($file_name, "r+")) {

			throw new CriticalSectionException("Semaphore cannot be created. Cause: 'Can not create file {$file_name}.'");
		}

		return flock($this->file[$num], LOCK_EX | LOCK_NB);
	}

}
