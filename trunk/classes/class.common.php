<?php
class button{
	public $id;
	function __construct($parent, $width, $height, $x, $y,  $color, $event){
		$this->parent = $parent;
		$this->x = $x;
		$this->y = $y;
		$this->width = $y;
		$this->height = $y;
		$this->color = $color;
		$this->event = $event;
		$this->id = xcb_generate_id();
		xcb_create_window($this->id, $this->parent , $this->width, $this->height, $this->x, $this->y, 1);
		xcb_configure_window_events($this->id, array(4));
		xcb_map_window($this->id);
		xcb_flush();
	}
}

?>