<?
	// ================================================================================= //

	/**
	 *	This class mimics the type casting functionality of java.
	 */
	class Caster
	{
	    function Cast($value, $asObject)
	    {
	        $value = (object) $value;
	        $value_array = explode(":", serialize($value));
	        $object_array = explode(":", serialize($asObject));
	        
	        $value_array[1] = $object_array[1];
	        $value_array[2] = $object_array[2];
	
	        $value_object = unserialize(implode(":", $value_array));
	
	        return $value_object;
	    }
	}
	
	// ================================================================================= //
?>