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
#include <xcb/xcb.h>
#include <vector>
int openDisplay();
void manageNewWindow(xcb_window_t);
void destroy();
int runscript (char*, int, char**);
int phpwm_config_width_in_pixels();
int phpwm_config_height_in_pixels();
int phpwm_config_white_pixel();
int phpwm_config_black_pixel();
extern std::vector<int> phpwm_window_list();
int phpwm_window_generate_id();
int phpwm_window_configure(int);
int phpwm_window_create_window(int, int, int, int, int, int);
int phpwm_window_map(int);
int phpwm_window_unmap(int);
int phpwm_move_window(int, int, int);
int phpwm_resize_window(int, int, int);
int phpwm_window_border(int, int);
int phpwm_raise_window(int);
int phpwm_lower_window(int);
int phpwm_reparent_window(int, int);
int phpwm_window_get_class(int);
int phpwm_get_parent_window(int);
int phpwm_get_child_window(int);
int phpwm_window_subscribe_events(int);
int phpwm_report_button_press(int, int);
int phpwm_report_button_release(int, int);
int phpwm_get_last_button_press(int);
int phpwm_get_last_button_release(int);
void phpwm_set_drag_state(int, int);
int phpwm_get_drag_state(int);
void phpwm_set_window_state(int, int);
int phpwm_get_window_state(int);

void watch_events();
extern xcb_connection_t *xconnection;
void event_exposure(xcb_generic_event_t*);
void event_button_press(xcb_generic_event_t*);
void event_button_release(xcb_generic_event_t*);
void event_key_press(xcb_generic_event_t*);
void event_key_release(xcb_generic_event_t*);
void event_configure_request(xcb_generic_event_t*);
void event_map_request(xcb_generic_event_t*);
void event_configure_notify(xcb_generic_event_t*);
void event_map_notify(xcb_generic_event_t*);
void event_create_notify(xcb_generic_event_t*);
void event_error(xcb_generic_event_t*);
void event_destroy_notify(xcb_generic_event_t*);
void event_unmap_notify(xcb_generic_event_t*);
void event_leave_notify(xcb_generic_event_t*);
void event_motion_notify(xcb_generic_event_t*);
void event_enter_notify(xcb_generic_event_t*);
