<?php 
namespace App\Util\IntervalTree;

use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Date range which excludes intersecting dates.
 */
class CarbonRange implements RangeInterface
{
  /**
   * @var Carbon
   */
  protected $start;
  
  /**
   * @var Carbon
   */
  protected $end;
  
  /**
   * @var CarbonInterval
   */
  protected $step;
  
  /**
   * @param Carbon     $start
   * @param Carbon     $end
   * @param CarbonInterval $step
   * @param bool $inclusive
   */
  public function __construct(Carbon $start, Carbon $end = null, CarbonInterval $step = null, $inclusive = true)
  {
    $this->start = clone $start;
    $this->end = ($end ? clone $end : $end);    
    
    if (!$inclusive) {
      $this->start->addMinute();
      if ($this->end !== null) {
        $this->end->subMinute();
      }
    }
    
    if ($step) {
      $this->step = $step;
    } else {      
      $this->step = CarbonInterval::minute(1);
    }    
  }
  
  /**
   * {@inheritDoc}
   *
   * @return \DateTime
   */
  public function getStart()
  {
    return $this->start;
  }
  
  /**
   * {@inheritDoc}
   *
   * @return \DateTime
   */
  public function getEnd()
  {
    return $this->end;
  }

  /**
   * {@inheritDoc}
   *
   * @return \Generator
   */
  public function iterable()
  {
    $date = clone $this->getStart();
        
    while ($date < $this->getEnd()) {
      yield $date;
      $date->add($this->step);
    }
  }
  
  
  public function applyCarbonInterval(CarbonInterval $ci)
  {
    $this->start->add($ci);
    $this->end->add($ci);
  }
  
  /**
   * @return string
   */
  public function __toString()
  {
    return $this->start->format('Y-m-d H:i').' .. '.$this->end->format('Y-m-d H:i');
  }
}