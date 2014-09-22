<?php
/**
* This file is part of the Bakame.Period library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/nyamsprod/Period/
* @version 0.3.0
* @package Bakame.Period
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Bakame;

use DateTime;
use DateInterval;
use DatePeriod;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;

/**
* A value object class to manipulate Date period
*
* @package Bakame.Period
* @since 0.1.0
*
*/
final class Period
{
    /**
     * The Range start date
     *
     * @var \DateTime
     */
    private $start;

    /**
     * The Range end date
     * This date is not included in the Period
     * It is the first DateTime object greater than
     * the last DateTime object included in the given
     * Period
     *
     * @var \DateTime
     */
    private $end;

    /**
     * The Constructor
     *
     * $period = new Period('2012-01-01', '2012-02-17');
     *
     * @param \DateTime|string $start start datetime
     * @param \DateTime|string $end   end datetime
     *
     * @throws \LogicException If $start is greater than $end
     */
    public function __construct($start, $end)
    {
        $start = self::validateDateTime($start);
        $end   = self::validateDateTime($end);
        if ($start > $end) {
            throw new LogicException('the end must be greater or equals to the start');
        }
        $this->start = clone $start;
        $this->end   = clone $end;
    }

    /**
     * Create a Period object from a starting point and an interval
     *
     * <code>
     *<?php
     * $period = Period::createFromDuration('2012-01-01', '3 MONTH');
     * $period = Period::createFromDuration(new DateTime('2012-01-01'), new DateInterval('P3M'));
     * $period = Period::createFromDuration(new DateTime('2012-01-01'), '3 MONTH');
     * $period = Period::createFromDuration('2012-01-01', new DateInterval('P3M'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string     $start    start date
     * @param \DateInterval|string $interval interval or a string understood by DateInterval::createFromDateString
     *
     * @return static
     */
    public static function createFromDuration($start, $interval)
    {
        $start = self::validateDateTime($start);
        $end   = clone $start;
        $end->add(self::validateDateInterval($interval));

        return new self($start, $end);
    }

    /**
     * Create a Period object from a Year and a Week
     *
     * <code>
     *<?php
     * $period = Period::createFromWeek(2012, 3);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $week index from 1 to 53
     *
     * @return static
     */
    public static function createFromWeek($year, $week)
    {
        $start = new DateTime;
        $start->setISODate(self::validateYear($year), self::validateRange($week, 1, 53));
        $start->setTime(0, 0, 0);

        return self::createFromDuration($start, '1 WEEK');
    }

    /**
     * Create a Period object from a Year and a Month
     *
     * <code>
     *<?php
     * $period = Period::createFromMonth(2012, 11);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $month Month index from 1 to 12
     *
     * @return static
     */
    public static function createFromMonth($year, $month)
    {
        $year  = self::validateYear($year);
        $month = self::validateRange($month, 1, 12);

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '1 MONTH');
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $period = Period::createFromQuarter(2012, 2);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $quarter Quarter Index from 1 to 4
     *
     * @return static
     */
    public static function createFromQuarter($year, $quarter)
    {
        $year    = self::validateYear($year);
        $quarter = self::validateRange($quarter, 1, 4);
        $month   = (($quarter - 1) * 3) + 1;

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '3 MONTHS');
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $period = Period::createFromSemester(2012, 1);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $semester Semester Index from 1 to 2
     *
     * @return static
     */
    public static function createFromSemester($year, $semester)
    {
        $year     = self::validateYear($year);
        $semester = self::validateRange($semester, 1, 2);
        $month    = (($semester - 1) * 6) + 1;

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '6 MONTHS');
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $period = Period::createFromYear(2012);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     *
     * @return static
     */
    public static function createFromYear($year)
    {
        return self::createFromDuration(self::validateYear($year).'-01-01', '1 YEAR');
    }

    /**
     * start date setter
     *
     * <code>
     *<?php
     * $period = Period::createFromSemester(2012, 1);
     * $newRange = $period->setStart('2012-02-01');
     * $altRange = $period->setStart(new DateTime('2012-02-01'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string $datetime
     *
     * @return static
     */
    public function setStart($datetime)
    {
        return new self(self::validateDateTime($datetime), $this->end);
    }

    /**
     * start date getter
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return clone $this->start;
    }

    /**
     * start end setter
     *
     * <code>
     *<?php
     * $period = Period::createFromSemester(2012, 1);
     * $newRange = $period->setEnd('2012-02-01');
     * $altRange = $period->setEnd(new DateTime('2012-02-01'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string $datetime
     *
     * @return static
     */
    public function setEnd($datetime)
    {
        return new self($this->start, self::validateDateTime($datetime));
    }

    /**
     * end date getter
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return clone $this->end;
    }

    /**
     * return a new Period with the same start
     * but with a different duration
     *
     * @param \DateInterval|string $interval interval or a string understood by DateInterval::createFromDateString
     *
     * @return static
     */
    public function setDuration($interval)
    {
        return self::createFromDuration($this->start, $interval);
    }

    /**
     * return the Period duration as a DateInterval object
     *
     * @return \DateInterval
     */
    public function getDuration()
    {
        return $this->start->diff($this->end);
    }

    /**
     * return the Datetime included in the Period
     * according to a given interval
     *
     * @param \DateInterval|string $interval
     *
     * @return \DatePeriod
     */
    public function getRange($interval)
    {
        return new DatePeriod(
            $this->start,
            self::validateDateInterval($interval),
            $this->end
        );
    }

    /**
     * Tells whether a DateTime is contained within the Period object
     *
     * <code>
     *<?php
     *   $obj = Period::createFromMonth(2014, 3);
     *   $obj->contains('2014-03-30'); //return true
     *   $obj->contains('2014-04-01'); //return false
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string $datetime
     *
     * @return boolean
     */
    public function contains($datetime)
    {
        $date = self::validateDateTime($datetime);

        return $date >= $this->start && $date < $this->end;
    }

    /**
     * Tell whether two Period objects overlaps
     *
     * @param Period $period
     *
     * @return boolean
     */
    public function overlaps(Period $period)
    {
        return $this->contains($period->start) || $this->contains($period->end);
    }

    /**
     * Merge two Period objects to return a new Period object
     * that englobes both Periods
     *
     * @param Period $period
     *
     * @return static
     */
    public function merge(Period $period)
    {
        $start = $this->start;
        if ($start > $period->start) {
            $start = $period->start;
        }
        $end = $this->end;
        if ($end < $period->end) {
            $end = $period->end;
        }

        return new self($start, $end);
    }

    /**
     * Validate a DateTime
     *
     * @param \DateTime|string $str
     *
     * @return \DateTime
     *
     * @throws \RuntimException If The Data can not be converted into a proper DateTime object
     */
    private static function validateDateTime($str)
    {
        if ($str instanceof Datetime) {
            return $str;
        }

        return new DateTime((string) $str);
    }

    /**
     * Validate a DateInterval
     *
     * @param \DateInterval|string $ttl
     *
     * @return \DateInterval
     *
     * @throws \RuntimException If The Data can not be converted into a proper DateInterval object
     */
    private static function validateDateInterval($ttl)
    {
        if ($ttl instanceof DateInterval) {
            return $ttl;
        }

        return DateInterval::createFromDateString((string) $ttl);
    }

    /**
     * Validate a year
     *
     * @param integer $year
     *
     * @return integer
     *
     * @throws \InvalidArgumentException If year is not a valid integer
     */
    private static function validateYear($year)
    {
        $year = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $year) {
            throw new InvalidArgumentException("A Year must be a valid integer");
        }

        return $year;
    }

    /**
     * Validate a integer according to a range
     *
     * @param integer $value the value to validate
     * @param integer $min   the minimun value
     * @param integer $max   the maximal value
     *
     * @return integer the validated value
     *
     * @throws \OutOfRangeException If the value is not in the range
     */
    private static function validateRange($value, $min, $max)
    {
        $res = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            array('options' => array('min_range' => $min, 'max_range' => $max))
        );
        if (false === $res) {
            throw new OutOfRangeException("please verify your value range");
        }

        return $res;
    }
}
