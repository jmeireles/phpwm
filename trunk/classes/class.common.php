<?php
class button{
	public $id;
	function __construct($objCore, $parent, $width, $height, $x, $y,  $color, $event){
		$objThis = $this;
		$this->_core = $objCore;
		$this->xcb = $objCore->xcb;
		$this->parent = $parent;
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
		$this->color = $color;
		$this->event = $event;
		$this->id = xcb_generate_id($this->xcb);
		xcb_create_window($this->xcb, $this->id, $this->parent , $this->width, $this->height, $this->x, $this->y, 1);
		$this->graphics = new graphics($this->_core, $this->id);
		$this->background = $this->graphics->rectangle( $this->color, array(0, 0, $this->width, $this->height));
		xcb_configure_window_events($this->xcb, $this->id, array(32768, 4));
		$this->_core->registerEvent($this->id, "onExpose", function($evt) use ($objThis){
			//if (isset($objThis->graphics)){
				$objThis->graphics->draw();
			//}
			xcb_flush($objThis->xcb);
		});
		xcb_map_window($this->xcb, $this->id);
		xcb_flush($this->xcb);
	}
}

?>