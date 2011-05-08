<?php
class graphics {
	private $_window, $_colormap, $_context;
	private $_elements = array();
	private $_colors = array();
	function __construct($core, $intWindow){
		$this->_core = $core;
		$this->xcb = $core->xcb;
		$this->_window = $intWindow;
		xcb_flush($this->xcb);

	}
	function _getColor($color){
		return $this->_core->_colors[$color];
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
	function resize($arrCords) {
		foreach($this->_elements as $element){
			$element->resize($arrCords);
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
		$this->_context = xcb_generate_id($this->xcb);
		xcb_create_gc($this->xcb, $this->_window, $this->_context, array(4, 8), array($fgcolor, $bgcolor));
		xcb_flush($this->xcb);
	}
	function resize($coords){
		$this->_coords = $coords;
	}
	function draw(){
		xcb_poly_fill_rectangle($this->xcb, $this->_window, $this->_context, $this->_coords[0], $this->_coords[1], $this->_coords[2], $this->_coords[3]);
		xcb_flush($this->xcb);
	}

}
?>