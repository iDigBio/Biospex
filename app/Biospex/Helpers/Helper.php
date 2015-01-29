<?php namespace Biospex\Helpers;
/**
 * Helper.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

class Helper {

    public static function sessionFlashPush($key, $value) {
        $values = Session::get($key, []);
        $values[] = $value;
        Session::flash($key, $values);
    }

	/**
	 * Round up to an integer, then to the nearest multiple of 5
	 * Used for scaling project page percent complete
	 *
	 * @param $n
	 * @param int $x
	 * @return float
	 */
	public static function roundUpToAnyFive($n,$x=5) {
		return (ceil($n)%$x === 0) ? ceil($n) : round(($n+$x/2)/$x)*$x;
	}

    public static function deleteDirectoryContents($dir, $ignore = ['.gitignore'])
    {
        if (false === file_exists($dir))
        {
            return false;
        }

        /** @var SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo)
        {
            if ($fileinfo->isDir())
            {
                if (false === rmdir($fileinfo->getRealPath()))
                {
                    return false;
                }
            } else
            {
                if (in_array($fileinfo->getFilename(), $ignore))
                {
                    continue;
                }

                if (false === unlink($fileinfo->getRealPath()))
                {
                    return false;
                }
            }
        }
    }
}