<?php
class graphics {
	private $_window, $_colormap, $_context;
	private $_elements = array();
	private $_colors = array();
	function __construct($core, $intWindow, $fg, $bg, $font){
		$this->_core = $core;
		$this->xcb = $core->xcb;
		$this->_window = $intWindow;
		$this->_fg = $this->_getColor($fg);
		$this->_bg = $this->_getColor($bg);
		$this->_font = $this->_core->getFont($font);
		$this->_context = xcb_generate_id($this->xcb);
		xcb_create_gc($this->xcb, $this->_window, $this->_context, XCB_GC_FOREGROUND|XCB_GC_BACKGROUND|XCB_GC_FONT, array($this->_fg, $this->_bg, $this->_font));
		xcb_flush($this->xcb);

	}
	function _getColor($color){
		return $this->_core->_colors[$color];
	}

	function rectangle($arrCoords){
		$shape = new graphics_filled_rectangle($this->xcb, $this->_context, $this->_window, $arrCoords);
		array_push($this->_elements, $shape);
		return $shape;
	}
	function text($arrCoords, $strText){
		$shape = new graphics_text($this->xcb, $this->_context, $this->_window, $arrCoords, $strText);
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
class graphics_text {
	private $_context, $_window, $_coords;
	function __construct($xcb, $context, $window, $coords, $text){
		$this->xcb = $xcb;
		$this->_window = $window;
		$this->_coords = $coords;
		$this->_context = $context;
		$this->_text = $text;
	}
	function resize($coords){
		$this->_coords = $coords;
	}
	function setText($strText){
		$this->_text = $strText;
		$this->draw();
	}
	function draw() {
		xcb_image_text_8($this->xcb, $this->_window, $this->_context, $this->_coords[0], $this->_coords[1], $this->_text);
		xcb_flush($this->xcb);
	}
}

class graphics_filled_rectangle {
	private $_context, $_window, $_coords;
	function __construct($xcb, $context, $window, $coords){
		$this->xcb = $xcb;
		$this->_window = $window;
		$this->_coords = $coords;
		$this->_context = $context;
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