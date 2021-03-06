<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Record\Associations;

use Lycan\Support\Inflect;

abstract class Collection extends \Lycan\Record\Associations implements Interfaces\Collection, \IteratorAggregate, \ArrayAccess
{
    protected $marked_for_save_objects=array();

    /**
     * Tries to execute missing methods 
     * from @see Lycan\Record\Association::result_set instance
     *
     * @return mixed the result of missing method if will find one.
     */
    public function __call($method, $args)
    {
        return $this->magic_method_call($method, $args, $this->all());
    }

    protected function all()
    {
        if (   null == $this->result_set 
            || $this->result_set instanceof \Lycan\Record\Query
        ){
            $this->result_set = $this->find()->all();
        }
        return $this->result_set;
    }

    public function setWith(\Lycan\Record\Collection $collection)
    {
        $this->result_set = $collection;
    }

    public function isEmpty()
    {
        return $this->all()->isEmpty();
    }

    public function count()
    {
        return $this->all()->count(); 
    }

    public function getIds() 
    {
        $association = $this->association;
        return $this->all()->toArray($association::$primary_key);
    }
    
    public function setIds(array $ids)
    {
    
    }

    public function clear()
    {
    }

    public function exists()
    {
    
    }

    public function add($object)
    {
        $this->add_with_offset($object);
    }

    public function delete($object)
    {
        $this->delete_with_offset($object);
    }

    public function build($attributes=array())
    {
        $class = $this->association;
        $new = new $class($attributes);
        $this->add($new);
        return $new;
    }

    public function create($attributes=array())
    {
        $class = $this->association;
        $new = new $class($attributes);
        $new->save();
        $this->add($new);
        return $new;
    }

    /**
     * IteratorAggregate
     */
    public function getIterator()
    {
        return $this->all();
    }

    /**
     * ArrayAccess
     */
    public function offsetExists ( $offset ) 
    {
        return $this->all()->offsetExists($offset);
    }

    public function offsetGet ( $offset )
    {
        return $this->all()->offsetGet($offset); 
    }

    public function offsetSet ( $offset , $value )
    {
        $this->add_with_offset($value, $offset);
    }

    public function offsetUnset ( $offset )
    {
        $object = $this[$offset];
        $this->delete_with_offset($object, $offset);
    }
}
