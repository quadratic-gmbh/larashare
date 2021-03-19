<?php namespace App\Util\IntervalTree;

/**
 * An Interval Tree implementation.
 * http://en.wikipedia.org/wiki/Interval_tree
 *
 * Based on:
 *  - https://github.com/tylerkahn/intervaltree-python
 *  - https://github.com/misshie/interval-tree
 *
 */
class IntervalTree
{
    /**
     * @var \App\Util\IntervalTree\TreeNode
     */
    protected $top_node;

    /**
     * @var callable
     */
    protected $comparator;

    /**
     * IntervalTree constructor.
     *
     * Pass in an array of RangeInterface compatible objects and
     * an optional comparator callable.
     *
     * @param array    $ranges
     * @param callable $comparator
     *
     * @return void
     */
    public function __construct(array $ranges, callable $comparator = null)
    {
        $this->comparator = $comparator;

        $this->top_node = $this->divideIntervals($ranges);
    }

    /**
     * Search for ranges that overlap the specified value or range.
     *
     * @param mixed $interval Either a RangeInterface or a value.
     *
     * @return array
     */
    public function search($interval)
    {
        if (is_null($this->top_node)) {
            return array();
        }

        $result = $this->findIntervals($interval);
        $result = array_values($result);

        usort($result, function (RangeInterface $a, RangeInterface $b) {
            $x = $a->getStart();
            $y = $b->getStart();

            $comparedValue = $this->compare($x, $y);

            if ($comparedValue == 0) {
                $x = $a->getEnd();
                $y = $b->getEnd();
                $comparedValue = $this->compare($x, $y);
            }

            return $comparedValue;
        });

        return $result;
    }
    
    /**
     * check if tree has any node that overlaps with given interval
     * 
     * @param mixed $interval
     */
    public function overlaps($interval)
    {
      if (is_null($this->top_node)) {
        return false;
      }
      
      return $this->overlapsInterval($this->top_node, $interval);
    }
    
    /**
     * 
     * @param mixed $interval
     */
    protected function overlapsInterval($node, $interval)
    {      
      $first = $interval->getStart();
      $last = $interval->getEnd();       
              
      // check whether the node values overlap with interval.
      foreach ($node->s_center as $k) {
        if ($this->compare($k->getStart(), $first) <= 0 && $this->compare($k->getEnd(), $last) >= 0) {
          return true;
        }
      }
      
      // compare point against node center to determine which child
      // node we should recurse through.
      $comparedValue = $this->compare($first, $node->x_center);
      
      if ($node->left_node && $comparedValue < 0 && $this->overlapsInterval($node->left_node, $interval)) {
        return true;        
      }
      
      if ($node->right_node && $comparedValue > 0 && $this->overlapsInterval($node->right_node, $interval)) {
        return true;        
      }
      
      return false;      
    }
    
    /**
     * Search for ranges that overlap the specified value or range.
     *
     * @param mixed $interval Either a RangeInterface or a value.
     *
     * @return array
     */
    public function intersects($interval)
    {
      if (is_null($this->top_node)) {
        return false;
      }
      
      return $this->intersectsInterval($interval);
    }

    /**
     * @param array $intervals
     *
     * @return \App\Util\IntervalTree\TreeNode|null
     */
    protected function divideIntervals(array $intervals)
    {
        if (count($intervals) === 0) {
            return null;
        }

        $x_center = $this->center($intervals);
        $s_center = array();
        $s_left = array();
        $s_right = array();

        foreach ($intervals as $k) {
            if ($k->getStart() > $k->getEnd()) {
                throw new NegativeRangeException(
                    'Range is negative (maybe you entered the range in reverse order?)'
                );
            }
            if ($this->compare($k->getEnd(), $x_center) < 0) {
                $s_left[] = $k;
            } elseif ($this->compare($k->getStart(), $x_center) > 0) {
                $s_right[] = $k;
            } else {
                $s_center[] = $k;
            }
        }

        return new TreeNode(
            $x_center,
            $s_center,
            $this->divideIntervals($s_left),
            $this->divideIntervals($s_right)
        );
    }

    /**
     * @param array $intervals
     *
     * @return mixed
     */
    protected function center(array $intervals)
    {
        usort($intervals, function (RangeInterface $a, RangeInterface $b) {
            return $this->compare(
                $a->getStart(),
                $b->getStart()
            );
        });

        return $intervals[count($intervals) >> 1]->getStart();
    }

    /**
     * check wether a given interval intersects with the tree
     * 
     * @param mixed $interval
     * 
     * @return bool
     */
    protected function intersectsInterval($interval)
    {
      if ($interval instanceof RangeInterface) {
        $first = $interval->getStart();
        $last = $interval->getEnd();
      } else {
        $first = $interval;
        $last = null;
      }
      
      $result = false;
      if (null === $last) {
        $result = $this->intersectsPoint($this->top_node, $first);        
      } else {        
        foreach ($interval->iterable() as $j) {
          $result = $this->intersectsInterval($j);          
          if ($result) {
            return $result;
          }
        }
      }
      
      return $result;
    }
    
    /**
     * @param mixed $interval
     *
     * @return mixed
     */
    protected function findIntervals($interval)
    {
        if ($interval instanceof RangeInterface) {
            $first = $interval->getStart();
            $last = $interval->getEnd();
        } else {
            $first = $interval;
            $last = null;
        }

        if (null === $last) {
            $result = $this->pointSearch($this->top_node, $first);
        } else {
            $result = array();
            foreach ($interval->iterable() as $j) {
                $result = array_merge($result, $this->findIntervals($j));
            }
        }

        return $result;
    }

    /**
     * check whether a given point is contained in any interval
     * @param mixed $point
     * @return boolean
     */
    public function containsPoint($point)
    {
      if (is_null($this->top_node)) {
        return false;
      }
      
      return $this->intersectsPoint($this->top_node, $point);
    }
    
    /**
     * @param \App\Util\IntervalTree\TreeNode $node
     * @param mixed                  $point
     *
     * @return bool
     */
    protected function intersectsPoint(TreeNode $node, $point)
    {           
      // check whether the node values overlap point.
      foreach ($node->s_center as $k) {
        if ($this->compare($k->getStart(), $point) <= 0 && $this->compare($k->getEnd(), $point) >= 0) {
          return true;
        }
      }
      
      // compare point against node center to determine which child
      // node we should recurse through.
      $comparedValue = $this->compare($point, $node->x_center);
      
      if ($node->left_node && $comparedValue < 0) {
        if($this->intersectsPoint($node->left_node, $point)) {
          return true;
        }
      }
      
      if ($node->right_node && $comparedValue > 0) {
        if ($this->intersectsPoint($node->right_node, $point)) {
          return true;
        }
      }
      
      return false;
    }
    
    /**
     * @param \App\Util\IntervalTree\TreeNode $node
     * @param mixed                  $point
     *
     * @return array
     */
    protected function pointSearch(TreeNode $node, $point)
    {
        $result = array();

        // check whether the node values overlap point.
        foreach ($node->s_center as $k) {
            if ($this->compare($k->getStart(), $point) <= 0 && $this->compare($k->getEnd(), $point) > 0) {
                $result[spl_object_hash($k)] = $k;
            }
        }

        // compare point against node center to determine which child
        // node we should recurse through.
        $comparedValue = $this->compare($point, $node->x_center);

        if ($node->left_node && $comparedValue < 0) {
            $result = array_merge(
                $result,
                $this->pointSearch($node->left_node, $point, $result)
            );
        }

        if ($node->right_node && $comparedValue > 0) {
            $result = array_merge(
                $result,
                $this->pointSearch($node->right_node, $point, $result)
            );
        }

        return $result;
    }

    /**
     * @param mixed $a
     * @param mixed $b
     *
     * return int
     */
    protected function compare($a, $b)
    {
        if (is_null($this->comparator)) {
            if ($a < $b) {
                return -1;
            }
            if ($a > $b) {
                return 1;
            }
            return 0;
        }

        return call_user_func($this->comparator, $a, $b);
    }
}
