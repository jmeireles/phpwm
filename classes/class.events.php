<?php
class phpwm_events {
	private $_core;
	public $_callbacks = array();
	function __construct($objCore){
		$this->_core = $objCore;
		$this->xcb = $objCore->xcb;
	}

	function evt_CreateNotify($evt){
		var_export($evt);
		if ($evt['override_redirect']==0){
			foreach($this->_core->windows as $win){
				if ($win->frame == $evt['window']){
					echo "Configureing Frame {$evt['window']} for window $win->id\n";
					//$win->configureFrame($evt);
					return;
				}
			}
			if ($evt['parent'] == $this->_core->root['id']){
				echo "Creating new window for ID {$evt['window']}\n";
				$this->_core->windows[$evt['window']]= new phpwm_window($this->_core, $evt['window']);
			}
		}
	}

	function evt_ConfigureRequest($evt){
		if (isset($this->_core->windows[$evt['window']])){
			$this->_core->windows[$evt['window']]->configure($evt);
		} else {

			echo "Window {$evt['window']} not found\n";
		}
	}

	function evt_ConfigureNotify($evt){
		//echo "window configured\n";
	}

	function evt_MapRequest($evt){
		if (isset($this->_core->windows[$evt['window']])){
			echo "Mapping existing window {$evt['window']}\n\n";
			$this->_core->windows[$evt['window']]->map();
		} else {
			//TODO: Check that the parent is root?
			xcb_map_window($evt['window']);
		}
	}

	function evt_ReparentNotify($evt){
		echo "Reparent successfull apparently\n";
	}

	function evt_MotionNotify($evt){
//		foreach($this->_core->windows as $win){
//			if ($win->frame == $evt['event']){
//				$win->drag($evt);
//				return;
//			}
//		}
//var_export($this->_callbacks);
		foreach($this->_callbacks['onMotionNotify'] as $callback){
			if ($callback['id'] == $evt['event']){
				$callback['callback']($evt);
			}
		}
	}

	function evt_ButtonPress($evt){
//		foreach($this->_core->windows as $win){
//			if ($win->frame == $evt['event']){
//				$win->raise($evt);
//				return;
//			}
//		}
		foreach($this->_callbacks['onButtonPress'] as $callback){
			if ($callback['id'] == $evt['event']){
				$callback['callback']($evt);
			}
		}
	}

	function evt_UnmapNotify($evt){
		var_export($evt);
		if (isset($this->_core->windows[$evt['window']])){
			$this->_core->windows[$evt['window']]->unmapFrame();
		}
	}

	function evt_DestroyNotify($evt){
		if (isset($this->_core->windows[$evt['window']])){
			$this->_core->windows[$evt['window']]->destroy();
		}
	}
	function evt_Expose($evt){
		if (isset($this->_core->windows[$evt['window']])){
			$this->_core->windows[$evt['window']]->expose();
		} else {
			foreach($this->_core->windows as $win){
				if ($win->frame == $evt['window']){
					$win->expose($evt);
					return;
				}
			}
		}
	}
}
?>