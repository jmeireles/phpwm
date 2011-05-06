<?php
class phpwm_window{
	private $_core;
	public $id, $x, $y, $width, $height, $frame;
	public $borderwidth = 2;
	public $titleheight = 12;
	function __construct($objCore, $intId){
		$this->_core = $objCore;
		$this->id = $intId;
		$this->xcb = $this->_core->xcb;
	}
	function configure($evt){
		echo "Configuring window {$this->id}\n";
		$this->width = $evt['width'];
		$this->height = $evt['height'];
		$this->x = $evt['x'];
		$this->y = $evt['y'];
		xcb_configure_window_size($this->xcb, $this->id, $evt['width'], $evt['height']);
		xcb_configure_window_border($this->xcb, $this->id, 0);
		//currently assuming this is a new root window that will be placed in a frame?
		xcb_configure_window_events($this->xcb, $this->id, array(32768, 4));
		xcb_flush($this->xcb);
	}
	function configureFrame($evt){
		var_export($evt);
		xcb_configure_window_size($this->xcb, $this->frame, $evt['width'], $evt['height']);
		xcb_configure_window_events($this->xcb, $this->id, array(32768, 4, 8, 64, 16, 32, 1, 2));
	}

	function exposeFrame($evt){
		//redraw our border
		/**
		xcb_flush();
		$red = xcb_alloc_color($intColorMap, 255, 0, 0);
		xcb_flush();
		$gcid = xcb_generate_id();
		xcb_create_gc($this->frame, $gcid, array(4, 65536, 8), array($red, 0, $red));
		xcb_flush();
		xcb_poly_fill_rectangle($this->frame, $gcid, 0, 0, $evt['width'], $evt['height']);
		xcb_flush();
		**/
	}

	function map(){
		echo "Window: map\n";
		$objThis = $this;
		$this->frame = xcb_generate_id($this->xcb);
		
		xcb_create_window($this->xcb, $this->frame, $this->_core->root['id'], ($this->width+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight), $this->x, $this->y, 1);

		xcb_configure_window_events($this->xcb, $this->frame, array(32768, 4, 8, 256));
echo "Window: step\n";
		xcb_reparent_window($this->xcb, $this->id, $this->frame, $this->borderwidth, $this->titleheight);
		xcb_map_window($this->xcb, $this->frame);
		xcb_map_window($this->xcb, $this->id);
		xcb_flush($this->xcb);
		$this->graphics = new graphics($this->_core, $this->frame);
		$this->background = $this->graphics->rectangle( "blue", array(0, 0, ($this->width+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight)));
		//$this->button = $this->graphics->rectangle("green", array($this->width-15, 2, 8, 8));
		$this->button = new button($this->_core, $this->frame, 20, 20, $this->width-2, 2, "green", null);
		
		$this->_core->registerEvent($this->button->id, "onButtonPress", function($evt) use ($objThis){
			$objThis->destroy();
		});

		$this->_core->registerEvent($this->frame, "onMotionNotify", function($evt) use ($objThis){
			$objThis->drag($evt);
		} );
		
		$this->_core->registerEvent($this->frame, "onButtonPress", function($evt) use ($objThis){
			$objThis->raise($evt);
			var_export($evt);
			$objThis->click_x = $evt['root_x'];
			$objThis->click_y = $evt['root_y'];
		});
		echo "Window: map finish\n";
	}

	function drag($evt){
		echo "Move window to {$evt['root_x']}, {$evt['root_y']}\n";
		if ($evt['event_y'] < $this->titleheight){
			xcb_configure_window_pos($this->xcb, $this->frame, $evt['root_x'], $evt['root_y']);
		} else if ($evt['event_y']>($this->height-$this->borderwidth)){
			xcb_configure_window_size($this->xcb, $this->frame, ($this->width+($this->borderwidth*2)), $evt['event_y']);
			xcb_configure_window_size($this->xcb, $this->id, ($this->width), $evt['event_y']);
			$this->height = $evt['event_y'];
			echo "Resize Down : {$evt['event_y']}\n";
		} else if ($evt['event_x']< $this->borderwidth){
			//todo:  need to move & resize here
			echo "Resize Left : {$evt['event_x']}\n";
			xcb_configure_window_size($this->xcb, $this->frame, (($this->width-$evt['event_y'])+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight));
			xcb_configure_window_size($this->xcb, $this->id, ($this->width-$evt['event_y']), $this->height);
		} else if ($evt['event_x']> $this->width){
			xcb_configure_window_size($this->xcb, $this->frame, ($evt['event_y']+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight));
			xcb_configure_window_size($this->xcb, $this->id, ($evt['event_y']), $this->height);
		}

		xcb_flush($this->xcb);
	}
	function expose($evt){
		echo "Expose Event: \n";
		var_export($evt);
		$this->exposeFrame($evt);
		$this->graphics->draw();
	}
	function raise($evt){
		xcb_configure_window_raise($this->xcb, $this->frame);
		xcb_flush($this->xcb);
	}
	function unmapFrame(){
		xcb_unmap_window($this->xcb, $this->frame);
		xcb_flush($this->xcb);
	}
	function destroyFrame(){
		xcb_destroy_window($this->xcb, $this->frame);
		xcb_flush($this->xcb);
	}
	function destroy(){
		$this->destroyFrame();
		echo "TODO:// Clean up any resources\n";
	}
}