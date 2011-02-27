/*
 *  phpwm -- the PHP Based Window Manager
 *  Copyright (C) 2011  Davin C. Thompson <dthompso99@gmail.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
#include <stdio.h>
#include <stdlib.h>
#include <xcb/xcb.h>
#include <xcb/xcb_keysyms.h>
#include <xcb/xcb_atom.h>
#include <xcb/xcb_icccm.h>
#include <iostream>
#include <sstream>
#include <map>
#include "phpwm.h"

using namespace std;

void watch_events() {
	xcb_generic_event_t *event;
	while ((event = xcb_wait_for_event(xconnection))) {
		//cout << "*** New Event *** " << xcb_event_get_label(event->response_type) << endl;
		//idealy, we should probably fork on events? so several can happen at once
		switch (event->response_type & ~0x80) {
		case XCB_BUTTON_PRESS:
			event_button_press(event);
			break;
		case XCB_BUTTON_RELEASE:
			event_button_release(event);
			break;
		case XCB_KEY_PRESS:
			event_key_press(event);
			break;
		case XCB_KEY_RELEASE:
			event_key_release(event);
			break;
		case XCB_CONFIGURE_REQUEST:
			event_configure_request(event);
			break;
		case XCB_MAP_REQUEST:
			event_map_request(event);
			break;
		case XCB_CONFIGURE_NOTIFY:
			event_configure_notify(event);
			break;
		case XCB_CREATE_NOTIFY:
			event_create_notify(event);
			break;
		case XCB_MAP_NOTIFY:
			event_map_notify(event);
			break;
		case XCB_UNMAP_NOTIFY:
			event_unmap_notify(event);
			break;
		case XCB_DESTROY_NOTIFY:
			event_destroy_notify(event);
			break;
		case XCB_LEAVE_NOTIFY:
			event_leave_notify(event);
			break;
		case XCB_MOTION_NOTIFY:
			event_motion_notify(event);
			break;
		case XCB_ENTER_NOTIFY:
			event_enter_notify(event);
			break;
		case 0:
			event_error(event);
			break;
		default:
			/* Unknown event type, ignore it */
			printf("Unknown event: %d\n", event->response_type);
			cout << "Unknown Event " << xcb_event_get_label(event->response_type) << endl;
			break;
		}
	}
	free(event);
}
void event_error(xcb_generic_event_t* evt) {
	xcb_generic_error_t *e = (xcb_generic_error_t *) evt;
	cout << "event_error" << e->error_code << endl;
}
void event_destroy_notify(xcb_generic_event_t* evt) {
	xcb_destroy_notify_event_t *e = (xcb_destroy_notify_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("pad0", e->pad0);
	Args.add("sequence", e->sequence);
	Args.add("event", e->event);
	Args.add("window", e->window);
	runscript((char*) "core.php", Args);
}
void event_unmap_notify(xcb_generic_event_t* evt) {
	xcb_unmap_notify_event_t *e = (xcb_unmap_notify_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("pad0", e->pad0);
	Args.add("sequence", e->sequence);
	Args.add("event", e->event);
	Args.add("window", e->window);
	Args.add("from_configure", e->from_configure);
	Args.add("pad1[3]", e->pad1[3]);
	runscript((char*) "core.php", Args);
}
void event_configure_notify(xcb_generic_event_t* evt) {
	xcb_configure_notify_event_t *e = (xcb_configure_notify_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("pad0", e->pad0);
	Args.add("sequence", e->sequence);
	Args.add("event", e->event);
	Args.add("window", e->window);
	Args.add("above_sibling", e->above_sibling);
	Args.add("x", e->x);
	Args.add("y", e->y);
	Args.add("width", e->width);
	Args.add("height", e->height);
	Args.add("border_width", e->border_width);
	Args.add("override_redirect", e->override_redirect);
	Args.add("pad1", e->pad1);
	runscript((char*) "core.php", Args);
}
void event_configure_request(xcb_generic_event_t* evt) {
	xcb_configure_request_event_t *e = (xcb_configure_request_event_t *) evt;
	//phpwm_window_configure(e->window);
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("stack_mode", e->stack_mode);
	Args.add("sequence", e->sequence);
	Args.add("parent", e->parent);
	Args.add("window", e->window);
	Args.add("sibling", e->sibling);
	Args.add("x", e->x);
	Args.add("y", e->y);
	Args.add("width", e->width);
	Args.add("height", e->height);
	Args.add("border_width", e->border_width);
	Args.add("value_mask", e->value_mask);
	runscript((char*) "core.php", Args);
}

void event_map_request(xcb_generic_event_t* evt) {
	xcb_map_request_event_t *e = (xcb_map_request_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("response_type", e->response_type);
	Args.add("pad0", e->pad0);
	Args.add("sequence", e->sequence);
	Args.add("parent", e->parent);
	Args.add("window", e->window);
	runscript((char*) "core.php", Args);
}

void event_button_press(xcb_generic_event_t* evt) {
	xcb_button_press_event_t *e = (xcb_button_press_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("detail", e->detail);
	Args.add("sequence", e->sequence);
	Args.add("time", e->time);
	Args.add("root", e->root);
	Args.add("event", e->event);
	Args.add("child", e->child);
	Args.add("root_x", e->root_x);
	Args.add("root_y", e->root_y);
	Args.add("event_x", e->event_x);
	Args.add("event_y", e->event_y);
	Args.add("state", e->state);
	Args.add("same_screen", e->same_screen);
	Args.add("pad0", e->pad0);
	runscript((char*) "core.php", Args);
}

void event_button_release(xcb_generic_event_t* evt) {
	xcb_button_release_event_t *e = (xcb_button_release_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("detail", e->detail);
	Args.add("sequence", e->sequence);
	Args.add("time", e->time);
	Args.add("root", e->root);
	Args.add("event", e->event);
	Args.add("child", e->child);
	Args.add("root_x", e->root_x);
	Args.add("root_y", e->root_y);
	Args.add("event_x", e->event_x);
	Args.add("event_y", e->event_y);
	Args.add("state", e->state);
	Args.add("same_screen", e->same_screen);
	Args.add("pad0", e->pad0);
	runscript((char*) "core.php", Args);
}

void event_key_press(xcb_generic_event_t* evt) {
	xcb_key_press_event_t *e = (xcb_key_press_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("detail", e->detail);
	Args.add("sequence", e->sequence);
	Args.add("time", e->time);
	Args.add("root", e->root);
	Args.add("event", e->event);
	Args.add("child", e->child);
	Args.add("root_x", e->root_x);
	Args.add("root_y", e->root_y);
	Args.add("event_x", e->event_x);
	Args.add("event_y", e->event_y);
	Args.add("state", e->state);
	Args.add("same_screen", e->same_screen);
	Args.add("pad0", e->pad0);
	runscript((char*) "core.php", Args);
}

void event_key_release(xcb_generic_event_t* evt) {
	xcb_key_release_event_t *e = (xcb_key_release_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("detail", e->detail);
	Args.add("sequence", e->sequence);
	Args.add("time", e->time);
	Args.add("root", e->root);
	Args.add("event", e->event);
	Args.add("child", e->child);
	Args.add("root_x", e->root_x);
	Args.add("root_y", e->root_y);
	Args.add("event_x", e->event_x);
	Args.add("event_y", e->event_y);
	Args.add("state", e->state);
	Args.add("same_screen", e->same_screen);
	Args.add("pad0", e->pad0);
	runscript((char*) "core.php", Args);
}

void event_create_notify(xcb_generic_event_t* evt) {
	xcb_create_notify_event_t *e = (xcb_create_notify_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("pad0", e->pad0);
	Args.add("sequence", e->sequence);
	Args.add("parent", e->parent);
	Args.add("window", e->window);
	//		Args.add("sibling", e->sibling);
	Args.add("x", e->x);
	Args.add("y", e->y);
	Args.add("width", e->width);
	Args.add("height", e->height);
	Args.add("border_width", e->border_width);
	Args.add("override_redirect", e->override_redirect);
	Args.add("pad1", e->pad1);
	runscript((char*) "core.php", Args);
}

void event_map_notify(xcb_generic_event_t* evt) {
	xcb_map_notify_event_t *e = (xcb_map_notify_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("pad0", e->pad0);
	Args.add("sequence", e->sequence);
	Args.add("event", e->event);
	Args.add("window", e->window);
	Args.add("override_redirect", e->override_redirect);
	Args.add("pad1[3]", e->pad1[3]);
	runscript((char*) "core.php", Args);

}
void event_enter_notify(xcb_generic_event_t* evt) {
	xcb_enter_notify_event_t *e = (xcb_enter_notify_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("detail", e->detail);
	Args.add("sequence", e->sequence);
	Args.add("time", e->time);
	Args.add("root", e->root);
	Args.add("event", e->event);
	Args.add("child", e->child);
	Args.add("root_x", e->root_x);
	Args.add("root_y", e->root_y);
	Args.add("event_x", e->event_x);
	Args.add("event_y", e->event_y);
	Args.add("state", e->state);
	Args.add("mode", e->mode);
	Args.add("same_screen_focus", e->same_screen_focus);
	runscript((char*) "core.php", Args);

}
void event_motion_notify(xcb_generic_event_t* evt) {
	xcb_motion_notify_event_t *e = (xcb_motion_notify_event_t *) evt;
	if (phpwm_get_drag_state((int) e->event) == 1){
		//do the window draging straight in C++, its allot of events
		phpwm_move_window((int) e->event, (int) e->event_x, (int) e->event_y);
	} else {
		php_args Args;
		Args = php_args();
		Args.add("response_type", xcb_event_get_label(e->response_type));
		Args.add("detail", e->detail);
		Args.add("sequence", e->sequence);
		Args.add("time", e->time);
		Args.add("root", e->root);
		Args.add("event", e->event);
		Args.add("child", e->child);
		Args.add("root_x", e->root_x);
		Args.add("root_y", e->root_y);
		Args.add("event_x", e->event_x);
		Args.add("event_y", e->event_y);
		Args.add("state", e->state);
		Args.add("same_screen", e->same_screen);
		Args.add("pad0", e->pad0);
		runscript((char*) "core.php", Args);
	}

}
void event_leave_notify(xcb_generic_event_t* evt) {
	xcb_leave_notify_event_t *e = (xcb_leave_notify_event_t *) evt;
	php_args Args;
	Args = php_args();
	Args.add("response_type", xcb_event_get_label(e->response_type));
	Args.add("detail", e->detail);
	Args.add("sequence", e->sequence);
	Args.add("time", e->time);
	Args.add("root", e->root);
	Args.add("event", e->event);
	Args.add("child", e->child);
	Args.add("root_x", e->root_x);
	Args.add("root_y", e->root_y);
	Args.add("event_x", e->event_x);
	Args.add("event_y", e->event_y);
	Args.add("state", e->state);
	Args.add("mode", e->mode);
	Args.add("same_screen_focus", e->same_screen_focus);
	runscript((char*) "core.php", Args);
}

