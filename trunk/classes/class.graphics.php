<?php
class graphics {
	private $_window, $_colormap, $_context;
	private $_elements = array();
	private $_colors = array();
	function __construct($core, $intWindow){
		$this->_core = $core;
		$this->xcb = $core->xcb;
		echo "graphics: xcb connection is {$this->xcb}\n";
		//var_export($this->xcb);
		$this->_window = $intWindow;
		echo "graphics: getting colormap\n";
		$this->_colormap = xcb_get_default_colormap($this->xcb);
		echo "graphics: got the colormap\n";
		//xcb_create_colormap($this->xcb, $this->_colormap, $this->_window);
		xcb_flush($this->xcb);
		
	}
	function _getColor($color){
		if (isset($this->_colors[$color])){
			return $this->_colors[$color]; 
		} else {
			echo "graphics: _getColor start \n";
			$this->_colors[$color] = xcb_alloc_named_color($this->xcb, $this->_colormap, $color);
			echo "graphics: _getColor end \n";
			return $this->_colors[$color]; 
		}
	}
	
	function rectangle($color, $arrCoords){
		$shape = new graphics_filled_rectangle($this->xcb, $this->_window, $this->_getColor($color), $this->_getColor($color), $arrCoords);
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
	function __construct($xcb, $window, $fgcolor, $bgcolor, $coords){
		$this->xcb = $xcb;
		$this->_window = $window;
		$this->_coords = $coords;
		echo "graphics: graphics_filled_rectangle:  generating ID\n";
		$this->_context = xcb_generate_id($this->xcb);
		echo "graphics: graphics_filled_rectangle:  creating context\n";
		xcb_create_gc($this->xcb, $this->_window, $this->_context, array(4, 8), array($fgcolor, $bgcolor));
		xcb_flush($this->xcb);
	}
	function draw(){
		echo "graphics: attempting to draw rectangle\n";
		xcb_poly_fill_rectangle($this->xcb, $this->_window, $this->_context, $this->_coords[0], $this->_coords[1], $this->_coords[2], $this->_coords[3]);
		xcb_flush($this->xcb);
	}

}
?>