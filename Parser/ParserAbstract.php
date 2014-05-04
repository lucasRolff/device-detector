<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace DeviceDetector\Parser;

use DeviceDetector\Cache\CacheInterface;
use DeviceDetector\Cache\CacheStatic;
use \Spyc;

abstract class ParserAbstract {

    protected $fixtureFile;
    protected $parserName;
    protected $userAgent;

    /**
     * @var CacheInterface
     */
    protected $cache;

    abstract public function parse();

    public function __construct($ua='')
    {
        $this->setUserAgent($ua);
    }

    public function setUserAgent($ua)
    {
        $this->userAgent = $ua;
    }

    public function getName()
    {
        return $this->parserName;
    }

    protected function getRegexes()
    {
        $regexList = $this->getCache()->get($this->parserName);
        if (empty($regexList)) {
            $regexList = Spyc::YAMLLoad($this->fixtureFile);
            $this->getCache()->set($this->parserName, $regexList);
        }
        return $regexList;
    }

    /**
     * Matches the useragent against the given regex
     *
     * @param $regex
     * @return bool
     */
    protected function matchUserAgent($regex)
    {
        // only match if useragent begins with given regex or there is no letter before it
        $regex = '/(?:^|[^A-Z_-])(?:' . str_replace('/', '\/', $regex) . ')/i';

        if (preg_match($regex, $this->userAgent, $matches)) {
            return $matches;
        }

        return false;
    }

    /**
     * @param string $item
     * @param array $matches
     * @return string type
     */
    protected function buildByMatch($item, $matches)
    {
        for ($nb=1;$nb<=3;$nb++) {
            if (strpos($item, '$' . $nb) === false) {
                continue;
            }

            $replace = isset($matches[$nb]) ? $matches[$nb] : '';
            $item = trim(str_replace('$' . $nb, $replace, $item));
        }
        return $item;
    }


    protected function buildVersion($versionString, $matches) {
        $versionString = $this->buildByMatch($versionString, $matches);

        $versionString = str_replace('_', '.', $versionString);

        return $versionString;
    }

    protected function preMatchOverall()
    {
        $regexes = $this->getRegexes();

        static $overAllMatch;

        if (empty($overAllMatch)) {
            $overAllMatch = $this->getCache()->get($this->parserName.'-all');
        }

        if (empty($overAllMatch)) {
            // reverse all regexes, so we have the generic one first, which already matches most patterns
            $overAllMatch = array_reduce(array_reverse($regexes), function($val1, $val2) {
                if (!empty($val1)) {
                    return $val1.'|'.$val2['regex'];
                } else {
                    return $val2['regex'];
                }
            });
            $this->getCache()->set($this->parserName.'-all', $overAllMatch);
        }

        return $this->matchUserAgent($overAllMatch);
    }

    /**
     * Sets the Cache class
     *
     * Note: The given class needs to have a 'get' and 'set' method to be used
     *
     * @param $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Returns Cache object
     *
     * @return CacheInterface
     */
    public function getCache()
    {
        if (!empty($this->cache)) {
            return $this->cache;
        }

        return new CacheStatic();
    }

}