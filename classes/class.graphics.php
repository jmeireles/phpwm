<?php
class graphics {
	private $_window, $_colormap, $_context;
	private $_elements = array();
	private $_colors = array();
	function __construct($intWindow){
		$this->_window = $intWindow;
		$this->_colormap = xcb_get_default_colormap();
		//xcb_create_colormap($this->_colormap, $this->_window);
		xcb_flush();
		
	}
	function _getColor($color){
		if (isset($this->_colors[$color])){
			return $this->_colors[$color]; 
		} else {
			$this->_colors[$color] = xcb_alloc_named_color($this->_colormap, $color);
			return $this->_colors[$color]; 
		}
	}
	
	function rectangle($color, $arrCoords){
		$shape = new graphics_filled_rectangle($this->_window, $this->_getColor($color), $this->_getColor($color), $arrCoords);
		array_push($this->_elements, $shape);
		return $shape;
	}
	
	function draw(){
		foreach($this->_elements as $element){
			$element->draw();
		}
	}
}

class graphics_generic_shape{

	
	
}

class graphics_filled_rectangle {
	private $_context, $_window, $_coords;
	function __construct($window, $fgcolor, $bgcolor, $coords){
		$this->_window = $window;
		$this->_coords = $coords;
		$this->_context = xcb_generate_id();
		xcb_create_gc($this->_window, $this->_context, array(4, 8), array($fgcolor, $bgcolor));
		xcb_flush();
	}
	function draw(){
		echo "attempting to draw at:". var_export($this->_coords, 1);
		xcb_poly_fill_rectangle($this->_window, $this->_context, $this->_coords[0], $this->_coords[1], $this->_coords[2], $this->_coords[3]);
xcb_flush();
	}

}
?>