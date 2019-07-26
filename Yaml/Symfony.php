<?php declare(strict_types=1);

/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 *
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace DeviceDetector\Yaml;

use Symfony\Component\Yaml\Yaml;

class Symfony implements Parser
{
    /**
     * Parses the file with the given filename using Symfony Yaml parser and returns the converted content
     *
     * @param string $file
     * @return mixed
     */
    public function parseFile(string $file)
    {
        return Yaml::parseFile($file);
    }
}
