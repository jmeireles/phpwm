<?php
class button{
	public $id;
	function __construct($objCore, $parent, $width, $height, $x, $y,  $color, $event){
		$this->_core = $objCore;
		$this->xcb = $objCore->xcb;
		$this->parent = $parent;
		$this->x = $x;
		$this->y = $y;
		$this->width = $y;
		$this->height = $y;
		$this->color = $color;
		$this->event = $event;
		$this->id = xcb_generate_id($this->xcb);
		xcb_create_window($this->xcb, $this->id, $this->parent , $this->width, $this->height, $this->x, $this->y, 1);
		xcb_configure_window_events($this->xcb, $this->id, array(4));
		xcb_map_window($this->xcb, $this->id);
		xcb_flush($this->xcb);
	}
}

?>