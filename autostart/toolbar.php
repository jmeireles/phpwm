<?php
class toolbar {
	function __construct($core, $arrArgs){
		$objThis = $this;
		$this->_core = $core;
		$this->xcb = $core->xcb;
		$this->width = xcb_root_width_in_pixels($this->xcb);
		$this->height = 25;
		$this->x = 0;
		$this->y = xcb_root_height_in_pixels($this->xcb)-$this->height;

		$this->body = xcb_generate_id($this->xcb);
		xcb_create_window($this->xcb, $this->body, $this->_core->root['id'], $this->width, $this->height, $this->x, $this->y, 1);
		xcb_configure_window_events($this->xcb, $this->body, array(32768));
		$this->graphics = new graphics($this->_core, $this->body, "Blue", "Black", "micro");
		$this->background = $this->graphics->rectangle(array(0, 0, $this->width, $this->height));
		xcb_map_window($this->xcb, $this->body);
		xcb_flush($this->xcb);

		$this->_core->registerEvent($this->body, "onExpose", function($evt) use ($objThis){
			$objThis->graphics->draw();
			xcb_flush($objThis->xcb);
		});

		$this->button = new button($this->_core, $this->body, 5, 5, $this->width-7, 2, "Blue", null);
		$this->_core->registerEvent($this->button->id, "onButtonPress", function($evt) use ($objThis){
			$objThis->_core->shutdown = true;
		});

	}
}
?>