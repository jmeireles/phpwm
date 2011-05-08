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
		$this->x = ($evt['x']==0?200:$evt['x']);
		$this->y = ($evt['y']==0?100:$evt['y']);
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
		//xcb_configure_window_pos($this->xcb, $this->frame, $this->x, $this->y);
	}

	function map(){
		$objThis = $this;
		$this->frame = xcb_generate_id($this->xcb);

		xcb_create_window($this->xcb, $this->frame, $this->_core->root['id'], ($this->width+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight), $this->x, $this->y, 1);

		

		xcb_reparent_window($this->xcb, $this->id, $this->frame, $this->borderwidth, $this->titleheight);

		
		
		xcb_map_window($this->xcb, $this->frame);

		xcb_map_window($this->xcb, $this->id);

		
		
		xcb_flush($this->xcb);
		
		$this->graphics = new graphics($this->_core, $this->frame);

		$this->background = $this->graphics->rectangle( "Green", array(0, 0, ($this->width+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight)));
		//$this->button = $this->graphics->rectangle("green", array($this->width-15, 2, 8, 8));
		$this->button = new button($this->_core, $this->frame, 5, 5, $this->width-7, 2, "Blue", null);
		$this->graphics->draw();
		xcb_flush($this->xcb);
		
		xcb_configure_window_events($this->xcb, $this->frame, array(32768, 4, 8, 256));
		xcb_configure_window_events($this->xcb, $this->id, array(524288));
		
		$this->_core->registerEvent($this->button->id, "onButtonPress", function($evt) use ($objThis){
			$objThis->destroy();
		});

		$this->_core->registerEvent($this->frame, "onMotionNotify", function($evt) use ($objThis){
			$objThis->drag($evt);
		} );

		$this->_core->registerEvent($this->id, "onDestroyNotify", function($evt) use ($objThis){
			$objThis->destroyFrame();
		} );

		$this->_core->registerEvent($this->frame, "onButtonPress", function($evt) use ($objThis){
			$objThis->raise($evt);
			//var_export($evt);
			$objThis->click_x = $evt['root_x'];
			$objThis->click_y = $evt['root_y'];
		});
		echo "Window: map finish\n";
	}

	function drag($evt){
		//echo "Move window to {$evt['root_x']}, {$evt['root_y']}\n";
		if ($evt['event_y'] < $this->titleheight){
			xcb_configure_window_pos($this->xcb, $this->frame, $evt['root_x'], $evt['root_y']);
		} else if ($evt['event_y']>($this->height-$this->borderwidth)){
			//Resize down
			$this->height = $evt['event_y']-$this->borderwidth-$this->titleheight;
			xcb_configure_window_size($this->xcb, $this->frame, ($this->width+($this->borderwidth*2)), $evt['event_y']+$this->borderwidth);
			xcb_configure_window_size($this->xcb, $this->id, $this->width, $this->height);
			$this->graphics->resize(array(0, 0, $this->width+($this->borderwidth*2), ($this->height+$this->borderwidth+$this->titleheight)));
		} else if ($evt['event_x']< $this->borderwidth){
			//todo:  need to move & resize here
			//echo "Resize Left : {$evt['event_x']}\n";
			//xcb_configure_window_size($this->xcb, $this->frame, (($this->width-$evt['event_y'])+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight));
			//xcb_configure_window_size($this->xcb, $this->id, ($this->width-$evt['event_y']), $this->height);
		} else if ($evt['event_x']> $this->width){
			echo "Resize Right : {$evt['event_x']}\n";
			$this->width = $evt['event_x'];
			xcb_configure_window_size($this->xcb, $this->frame, ($evt['event_x']+($this->borderwidth*2)), ($this->height+$this->borderwidth+$this->titleheight));
			xcb_configure_window_size($this->xcb, $this->id, $this->width, $this->height);
			xcb_configure_window_pos($this->xcb, $this->button->id, $this->width-7, 2);
			$this->graphics->resize(array(0, 0, $this->width+($this->borderwidth*2), ($this->height+$this->borderwidth+$this->titleheight)));
		}

		xcb_flush($this->xcb);
	}
	function expose($evt){
		if (isset($this->graphics)){
			$this->graphics->draw();
		}
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